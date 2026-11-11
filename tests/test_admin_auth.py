"""Run with python3 tests/test_admin_auth.py; requires PHP, no database."""

from http.cookiejar import CookieJar
from pathlib import Path
import os
import re
import socket
import subprocess
import tempfile
import time
import unittest
from urllib.error import HTTPError, URLError
from urllib.parse import urlencode, urljoin
from urllib.request import HTTPRedirectHandler, HTTPCookieProcessor, Request, build_opener


class NoRedirect(HTTPRedirectHandler):
    def redirect_request(self, request, fp, code, message, headers, new_url):
        return None


class AdminAuthTests(unittest.TestCase):
    protected_pages = ('home', 'manage-stores', 'manage-users', 'store-requests')
    test_key = 'admin-integration-test-key'

    @classmethod
    def setUpClass(cls):
        cls.root = Path(__file__).resolve().parents[1]
        cls.temporary = tempfile.TemporaryDirectory(prefix='greendash-admin-test-')
        cls.addClassCleanup(cls.temporary.cleanup)
        cls.log = open(Path(cls.temporary.name) / 'server.log', 'w+')
        cls.addClassCleanup(cls.log.close)
        with socket.socket() as listener:
            listener.bind(('127.0.0.1', 0))
            port = listener.getsockname()[1]
        environment = dict(os.environ, GREENDASH_ADMIN_KEY=cls.test_key)
        cls.server = subprocess.Popen(
            ['php', '-d', 'session.save_path=' + cls.temporary.name,
             '-d', 'display_errors=1', '-S', f'127.0.0.1:{port}',
             '-t', str(cls.root.parent)],
            stdout=cls.log, stderr=cls.log, env=environment,
        )
        cls.addClassCleanup(cls.stop_server)
        # Serve the project under a real subfolder, matching the VM deployment.
        cls.base = f'http://127.0.0.1:{port}/{cls.root.name}/'
        for _ in range(50):
            try:
                with build_opener().open(cls.base + 'controller-admin/login.php', timeout=1):
                    return
            except URLError:
                if cls.server.poll() is not None:
                    break
                time.sleep(0.05)
        cls.log.seek(0)
        raise RuntimeError('PHP test server did not start: ' + cls.log.read())

    @classmethod
    def stop_server(cls):
        cls.server.terminate()
        try:
            cls.server.wait(timeout=5)
        except subprocess.TimeoutExpired:
            cls.server.kill()
            cls.server.wait()

    def setUp(self):
        self.cookies = CookieJar()
        self.client = build_opener(NoRedirect(), HTTPCookieProcessor(self.cookies))

    def request(self, path, fields=None, method=None):
        data = urlencode(fields).encode() if fields is not None else None
        request = Request(urljoin(self.base, path), data=data, method=method)
        try:
            response = self.client.open(request, timeout=5)
        except HTTPError as error:
            response = error
        with response:
            body = response.read().decode()
            self.assertNotIn('Warning:', body)
            self.assertNotIn('Fatal error:', body)
            return response.status, response.headers, body

    def csrf(self, body):
        token = re.search(r'name="csrfToken" value="([a-f0-9]+)"', body)
        self.assertIsNotNone(token)
        return token.group(1)

    def session_id(self):
        return next(cookie.value for cookie in self.cookies if cookie.name == 'PHPSESSID')

    def login(self):
        status, _, body = self.request('controller-admin/login.php')
        self.assertEqual(status, 200)
        old_session = self.session_id()
        status, headers, _ = self.request('controller-admin/login.php', {
            'csrfToken': self.csrf(body), 'adminKey': self.test_key,
        })
        self.assertEqual(status, 303)
        self.assertEqual(headers['Location'], 'home.php')
        self.assertNotEqual(self.session_id(), old_session)

    def test_unauthenticated_pages_and_legacy_urls_are_protected(self):
        for page in self.protected_pages:
            with self.subTest(page=page):
                status, headers, body = self.request(f'controller-admin/{page}.php')
                self.assertEqual(status, 302)
                self.assertEqual(headers['Location'], 'login.php')
                self.assertNotIn('<html', body)
                status, headers, body = self.request(f'app/views/admin/{page}.php')
                self.assertEqual(status, 307)
                self.assertEqual(
                    urljoin(self.base + f'app/views/admin/{page}.php', headers['Location']),
                    self.base + f'controller-admin/{page}.php',
                )
                self.assertNotIn('<html', body)

    def test_client_cookie_cannot_grant_admin_access(self):
        self.client.addheaders = [('Cookie', 'adminAuthenticated=true; admin_authenticated=true')]
        self.assertEqual(self.request('controller-admin/home.php')[0], 302)

    def test_login_rejects_missing_csrf_and_invalid_keys(self):
        _, _, body = self.request('controller-admin/login.php')
        token = self.csrf(body)
        self.assertEqual(self.request('controller-admin/login.php', {
            'adminKey': self.test_key,
        })[0], 403)
        for key, message in [('', 'Please enter the admin key.'),
                             ('greendashN24', 'Incorrect admin key.')]:
            status, _, body = self.request('controller-admin/login.php', {
                'csrfToken': token, 'adminKey': key,
            })
            self.assertEqual(status, 200)
            self.assertIn(message, body)
            self.assertEqual(self.request('controller-admin/home.php')[0], 302)
        self.assertEqual(self.request('controller-admin/login.php', {
            'csrfToken': token, 'adminKey[]': self.test_key,
        })[0], 200)

    def test_successful_login_grants_all_admin_pages(self):
        self.login()
        for page in self.protected_pages:
            with self.subTest(page=page):
                status, headers, body = self.request(f'controller-admin/{page}.php')
                self.assertEqual(status, 200)
                self.assertEqual(headers['Cache-Control'], 'no-store')
                self.assertIn('action="logout.php" method="post"', body)
                self.assertIn('data-assets-path="../assets/"', body)
                self.csrf(body)
        status, headers, _ = self.request('controller-admin/login.php')
        self.assertEqual(status, 303)
        self.assertEqual(headers['Location'], 'home.php')

    def test_logout_requires_post_and_csrf_then_revokes_access(self):
        self.login()
        _, _, body = self.request('controller-admin/home.php')
        token = self.csrf(body)
        self.assertEqual(self.request('controller-admin/logout.php')[0], 405)
        self.assertEqual(self.request('controller-admin/logout.php', {'csrfToken': 'invalid'})[0], 403)
        self.assertEqual(self.request('controller-admin/home.php')[0], 200)
        status, headers, _ = self.request('controller-admin/logout.php', {'csrfToken': token})
        self.assertEqual(status, 303)
        self.assertEqual(headers['Location'], 'login.php')
        for page in self.protected_pages:
            self.assertEqual(self.request(f'controller-admin/{page}.php')[0], 302)

    def test_login_form_and_assets_work_under_project_subfolder(self):
        status, headers, body = self.request('controller-admin/login.php')
        self.assertEqual(status, 200)
        self.assertIn('action="login.php" method="post"', body)
        self.assertIn('HttpOnly', headers['Set-Cookie'])
        self.assertIn('SameSite=Lax', headers['Set-Cookie'])
        self.assertNotIn(self.test_key, body)
        self.assertEqual(self.request('js/admin-login.js')[0], 200)
        self.assertEqual(self.request('style/admin-login.css')[0], 200)
        self.assertEqual(self.request('bootstrap/css/bootstrap-5.3.3.min.css')[0], 200)


if __name__ == '__main__':
    unittest.main(verbosity=2)

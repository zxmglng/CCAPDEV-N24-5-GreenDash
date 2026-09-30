# CCAPDEV-N24-5-GreenDash
## Changelog
All significant changes to this project will be logged in this section

#### 2026-09-30
**Added**
- Initial files (landing page index.html, styles and js directories with respective .css and .js files)

## Task Distribution -- Frontend
### Shared / Group Features
- [ ] Light/Dark Mode Toggle
  - [ ] Toggle between light and dark themes
  - [ ] Apply theme consistently across all pages

- [ ] Toast Notifications
  - [ ] Success messages
  - [ ] Error messages
  - [ ] Warning/informational messages

- [ ] Quick Search
  - [ ] Search stalls from the homepage
  - [ ] Display matching stalls/results

- [ ] Responsive Design
  - [ ] Desktop layout
  - [ ] Mobile layout

- [ ] Centralized Styling
  - [ ] Shared `global.css` for background, typography, headers, containers, buttons, cards, shadows, borders, etc.
  - [ ] Page/role-specific CSS may be added when necessary


## Zoe — User: Account & Stalls
### Pages
- User Sign Up
- User Home
- Stalls Directory
- Stall Details
- Profile/account page

### Features
- Login/Logout
- DLSU email validation (mock)
- Stall cards
- Open/closed status
- Operating hours
- Stall search/filter
- Navigation between stalls

## Chelsea — User: Ordering & Checkout
### Pages
- Ordering page
- Checkout page
- Order confirmation

### Features
- Food selection
- Quantity
- Cart/order summary
- Pickup now
- Scheduled pickup
- Cash/digital payment selection
- Stall QR code
- Validation
- Success/error messages
- Remove/update cart items
- Compute total price
- Prevent scheduled pickup before current time/outside open hours
- Order note (OPTIONAL)

## Person 3 — Orders + Vendor Order Management
### User side
- Orders page
- Ongoing orders
- Past orders

### Vendor side
- Order Management
- Active orders
- Sort by pickup time/status
- Pick-up screen
- Enter Order ID
- Verify order
- Mark order as collected
- Rating only for completed order
- Order status progression (pending, preparing, ready, etc)


## Person 4 — Vendor Store + Admin
### Vendor
- Vendor Sign Up
- Account Details
- Manage Store
- Request to Add New Store
- Edit menu/inventory
- Edit operating hours
- Open/closed toggle
- Vendor dashboard
- Add menu item toggle, not only edit menu

### Admin
- Admin Home
- Manage Stalls
- Stall Requests
- Approve/deny requests
- Admin can request details before approval/denial

---
### Backend
AS OF 2026-09-30: No backend needed yet

## Implementation Notes
- Phase 1 focuses exclusively on frontend implementation.
- Authentication, database operations, payment processing, and backend APIs are simulated.
- Mock data may be implemented using JavaScript arrays, JSON files, or placeholder data.
- All pages must be accessible through the application's navigation/buttons.
- All pages must be responsive for desktop and mobile devices.
- External libraries and assets must be hosted locally, except for Chart.js if used.

const closedStallMessage = document.querySelector('#closedStallMessage');
const stallList = document.querySelector('.stall-list');
const locationSelect = document.querySelector('.location-select');

const nearestLocations = {
    razon: ['green court', 'agno', 'bloemen'],
    gokongwei: ['agno', 'green court', 'bloemen'],
    andrew: ['green court', 'agno', 'bloemen'],
    'la salle hall': ['bloemen', 'agno', 'green court'],
    bloemen: ['bloemen', 'agno', 'green court'],
    yuchengco: ['bloemen', 'agno', 'green court']
};

function sortStallsByLocation(selectedLocation) {
    if (!stallList) {
        return;
    }

    const preferredLocations = nearestLocations[selectedLocation.toLowerCase()] || [];
    const stallCards = [...stallList.querySelectorAll('.stall-card')];

    stallCards
        .map((card, originalIndex) => ({
            card,
            originalIndex,
            rank: preferredLocations.indexOf(card.dataset.location.toLowerCase())
        }))
        .sort((first, second) => {
            const firstRank = first.rank === -1 ? preferredLocations.length : first.rank;
            const secondRank = second.rank === -1 ? preferredLocations.length : second.rank;
            return firstRank - secondRank || first.originalIndex - second.originalIndex;
        })
        .forEach(({ card }) => stallList.appendChild(card));
}

if (locationSelect) {
    const savedLocation = localStorage.getItem('greendash-location');
    sortStallsByLocation(savedLocation || locationSelect.value);
    locationSelect.addEventListener('change', () => {
        sortStallsByLocation(locationSelect.value);
    });
}

document.querySelectorAll('.view-stall-button').forEach((viewStallButton) => {
    viewStallButton.addEventListener('click', (event) => {
        const stallCard = viewStallButton.closest('.stall-card');
        const closedStatus = stallCard.querySelector('.status.closed');

        if (closedStatus) {
            event.preventDefault();
            closedStallMessage.hidden = false;
        }
    });
});

document.querySelector('#closeClosedStallMessage').addEventListener('click', () => {
    closedStallMessage.hidden = true;
});

closedStallMessage.addEventListener('click', (event) => {
    if (event.target === closedStallMessage) {
        closedStallMessage.hidden = true;
    }
});

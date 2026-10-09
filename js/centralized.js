let stallData = [
    {
        name: "Starbucks",
        description: "A popular coffeehouse chain known for its specialty coffee drinks and pastries.",
        location: "Green Court (Fidel A. Reyes Street",
        image: "../../assets/mock-data/starbucks.png"
    },
    {
        name: "McDonald's",
        description: "An American fast-food restaurant chain known for its hamburgers, french fries, and milkshakes.",
        location: "La Salle Hall",
        image: "../../assets/mock-data/mcdonalds.webp"
    },
    {
        name: "Jollibee",
        description: "A Filipino multinational chain of fast food restaurants known for its fried chicken, spaghetti, and burgers.",
        location: "Green Court (Fidel A. Reyes Street)",
        image: "../../assets/mock-data/jollibee.png"
    },
    {
        name: "KFC",
        description: "A global fast-food restaurant chain that specializes in fried chicken and other chicken-based dishes.",
        location: "La Salle Hall",
        image: "../../assets/mock-data/kfc.png"
    },
    {
        name: "Greenwich",
        description: "A Filipino pizza and pasta chain known for its affordable and delicious menu items.",
        location: "Green Court (Fidel A. Reyes Street)",
        image: "../../assets/mock-data/greenwich.png"
    }
]

const lucideScript = document.createElement('script');
lucideScript.src = 'https://unpkg.com/lucide@latest';
lucideScript.onload = () => lucide.createIcons();
document.head.appendChild(lucideScript);

// display stall cards

function displayStallCards(){
    const stallCard = document.getElementById('stalls-card');
    const stallContainer = stallCard?.querySelector('.card-body');
    if (!stallContainer) return;

    stallData.forEach(stall => {
        const stallLink = document.createElement('a');
        stallLink.className = 'stall-card-link';
        stallLink.href = '../../pages/user/store-info.html';

        stallLink.innerHTML = `
            <div class="stall-card row flex-nowrap align-items-center">
                <div class="col-2">
                    <img class="stall-logo img-fluid" src="${stall.image}" alt="${stall.name}">
                </div>
                <div class="col-10 stall-details">
                    <h3 class="stall-name">${stall.name}</h3>
                    <p class="stall-description">${stall.description}</p>
                    <p class="stall-location">${stall.location}</p>
                </div>
            </div>
        `;

        stallContainer.appendChild(stallLink);
    });
}

displayStallCards();
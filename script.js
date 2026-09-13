/* ===================================================================
   TravelBuddies — shared script
   Loaded on every page. Each section below only touches the DOM it
   owns, and checks that DOM exists before wiring anything up, so one
   script.js can be safely included by home, auth, guide, and profile
   pages.
   =================================================================== */

/* -------------------------------------------------------------------
   Shared destination data + helpers
   Used by both the home page (featured picks) and the full guide
   (search/filter/modal), so they live at module scope instead of
   being locked inside one page's init function.
   ------------------------------------------------------------------- */

const destinations = [
    // Nature, Falls, Caves & Hiking
    { id: "biak-na-bato", municipality: "San Miguel", name: "Biak-Na-Bato National Park", description: "Limestone trails, deep riverbeds, and historic caverns — wide trails and river crossings to explore.", location: "Brgy. Poblacion, Biak-na-Bato, San Miguel, 3011 Bulacan", category: "Nature", tag: "Caves & trails", time: "Full-day adventure", imageUrl: "https://i.pinimg.com/1200x/a3/36/4d/a3364d27bc8d86a697ae1a5c71a177a0.jpg" },
    { id: "tila-pilon-hills", municipality: "Doña Remedios Trinidad", name: "Tila Pilon Hills", description: "Open hills with sweeping ridgelines — a quiet place for sunrise and panoramic views.", location: "Doña Remedios Trinidad, Bulacan", category: "Nature", tag: "Hillwalk", time: "Sunrise", imageUrl: "https://i.pinimg.com/736x/1b/d0/bc/1bd0bc1a3cf5dcac5b08fe627b2c30cd.jpg" },
    { id: "malangaan-cave", municipality: "San Rafael", name: "Malangaan Cave and Spring / Mount Secret", description: "Cavern passages and a cold spring tucked into ridge-side forests.", location: "San Rafael, Bulacan", category: "Nature", tag: "Cave & spring", time: "Half-day hike", imageUrl: "https://i.pinimg.com/1200x/7b/e0/11/7be011438f2f71171ef846be270860f4.jpg" },
    { id: "kabayunan-view-deck", municipality: "Doña Remedios Trinidad", name: "Kabayunan View Deck", description: "Highland viewpoint famous for sea-of-clouds mornings and camping.", location: "Brgy. Kabayunan, Doña Remedios Trinidad, Bulacan", category: "Nature", tag: "Viewpoint", time: "Sunrise", imageUrl: "https://i.pinimg.com/736x/fe/0f/a7/fe0fa725457ec0061093f9ed66bbd3b7.jpg" },
    { id: "angeland-kareta-falls", municipality: "Doña Remedios Trinidad", name: "Angeland Kareta Falls Nature Park", description: "A forested falls with shallow pools and natural swimming spots.", location: "Brgy. Camachile, Doña Remedios Trinidad, 3009 Bulacan", category: "Nature", tag: "Waterfall", time: "Half-day", imageUrl: "https://i.pinimg.com/736x/df/95/02/df95023e57ad7dea0895b9f83204590a.jpg" },
    { id: "digos-hills", municipality: "Doña Remedios Trinidad", name: "Digo's Hills (Verdivia Trail)", description: "Ridge trails and local viewpoints on the Verdivia route — good for short treks.", location: "Verdivia Trail, Doña Remedios Trinidad, Bulacan", category: "Nature", tag: "Hiking", time: "Morning trek", imageUrl: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQbpuKj7Dfn9RcAlRMaKW3fhlMfq24c6pXSEA4ryY599xfRiqRodR8EFSZV&s=10" },
    { id: "secret-falls-drt", municipality: "Doña Remedios Trinidad", name: "Secret Falls DRT", description: "A tucked-away waterfall popular with local hikers and picnickers.", location: "Doña Remedios Trinidad, Bulacan", category: "Nature", tag: "Hidden waterfall", time: "Half-day", imageUrl: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ2HzD9f_yEdFMAXnpmclIGpXu12j1CZennpJfT5zqL12bVoKkyFgwCIHk&s=10" },
    { id: "pinagrealan-cave", municipality: "Norzagaray", name: "Pinagrealan Cave", description: "A karst cave system of historical importance and rugged chambers.", location: "1562 Curvada Rd, Norzagaray, Bulacan", category: "Nature", tag: "Caving", time: "Guide recommended", imageUrl: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRJA_r9ghSc58JKTNtqFKlkeu4l_X9zJJlfVd62p4jA0Qe7ICEj3kRblZk&s=10" },
    { id: "mount-manalmon", municipality: "Doña Remedios Trinidad", name: "Mount Manalmon", description: "Famous for its scenic summit ridges and vantage points over nearby valleys.", location: "Doña Remedios Trinidad, 3009 Bulacan", category: "Nature", tag: "Summit hike", time: "Half-day to full-day", imageUrl: "https://cdn.mountains.com.ph/photos/c8072161-8183-40f4-859d-9c951ee77ddb/conversions/3044bd930b7f810d94905f73b68c6235-landscape.jpg" },
    { id: "tungtong-falls", municipality: "SJDM", name: "Tungtong Falls", description: "A scenic, accessible waterfall for a cool dip after a short walk.", location: "SJDM, Bulacan", category: "Nature", tag: "Waterfall", time: "Short visit", imageUrl: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ7MlC9HQ4inVT-JsDkh8AKUMisY7gxYRGJ7p6Lix_4nQ5lhxSGuupsnS4d&s=10" },
    { id: "madlum-cave", municipality: "Doña Remedios Trinidad", name: "Madlum Cave", description: "A network of caves and river channels — popular for exploration and swimming.", location: "Doña Remedios Trinidad, Bulacan", category: "Nature", tag: "Cave & river", time: "Full-day", imageUrl: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSKFvvdwVpJjAsYkKmYlOl4vR036P0My7GuV0UCVUkP8pZLje2VbK_K_0HQ&s=10" },
    { id: "bahay-paniki-cave", municipality: "Doña Remedios Trinidad", name: "Bahay Paniki Cave", description: "A cavern near the hanging bridge; a compact but atmospheric spelunking spot.", location: "2nd Hanging Bridge, Doña Remedios Trinidad, Bulacan", category: "Nature", tag: "Cave", time: "Short visit", imageUrl: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTpB-xY693kNV-ueJE9ygM5Y3S3X3orMqm4715xmTq39wRpCXiGV0UZXV6M&s=10" },

    // Historical & Heritage Sites
    { id: "casa-real-shrine", municipality: "Malolos", name: "Museo ng Kasaysayang Pampulitika ng Pilipinas – Casa Real Shrine", description: "A museum preserving the political history of the province inside a colonial-era house.", location: "Paseo del Congreso, Plaza Rizal, Malolos, 3000 Bulacan", category: "Heritage", tag: "Museum", time: "1 hour", imageUrl: "https://static.where-e.com/Philippines/Central_Luzon_Region/Bulakan/Museo-Ng-Kasaysayang-Pampulitika-Ng-Pilipinas-Casa-Real-Shrine_064d829fad00e5e6c14355197d297840.jpg" },
    { id: "kalayaan-tree", municipality: "Malolos", name: "Kalayaan Tree", description: "A local landmark with ties to civic memory and open plaza grounds.", location: "Malolos, Bulacan", category: "Heritage", tag: "Landmark", time: "Quick stop", imageUrl: "https://dynamic-media-cdn.tripadvisor.com/media/photo-o/07/ce/6d/a9/the-old-tree-and-historical.jpg?w=1200&h=1200&s=1" },
    { id: "museo-republika-1899", municipality: "Malolos", name: "Museo ng Republika ng 1899", description: "Dedicated to the first Philippine republic and period artifacts.", location: "Malolos, 3000 Bulacan", category: "Heritage", tag: "History", time: "45 minutes", imageUrl: "https://dynamic-media-cdn.tripadvisor.com/media/photo-o/2c/bb/38/69/caption.jpg?w=1200&h=-1&s=1" },
    { id: "marcelo-del-pilar-statue", municipality: "Malolos", name: "Marcelo H. Del Pilar Statue", description: "A statue and small park honoring the Bulacan-born propagandist.", location: "Provincial Capitol Grounds, Malolos, Bulacan", category: "Heritage", tag: "Monument", time: "Short visit", imageUrl: "https://businessmirror.com.ph/wp-content/uploads/2026/08/Marcelo-H.-Del-Pilar-Shrine-001.webp" },
    { id: "gregorio-del-pilar-monument", municipality: "Malolos", name: "Monument of Gregorio Del Pilar", description: "A commemorative monument in the civic heart of Malolos.", location: "Capitol Rd, Malolos, Bulacan", category: "Heritage", tag: "Memorial", time: "Quick stop", imageUrl: "https://live.staticflickr.com/5528/9479830513_5839a6afcf_b.jpg" },
    { id: "sevilla-mansion", municipality: "San Miguel", name: "Heritage Pockets of San Miguel (Sevilla Mansion)", description: "A preserved mansion and nearby heritage streets worth a slow walk.", location: "cor. Fulgencio & Tecson St, San Miguel, Bulacan", category: "Heritage", tag: "Ancestral home", time: "30 minutes", imageUrl: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQFr1VgsE8SCj_mR3hrvXsbAI2lVhv4VwFa2AY8F4L_5fK3U2ubCZQr_7OL&s=10" },
    { id: "ycasiano-enriquez-house", municipality: "Bulakan", name: "Ycasiano-Enriquez Ancestral House", description: "A preserved ancestral home showcasing local domestic architecture.", location: "Matungao St, Bulakan, Bulacan", category: "Heritage", tag: "House museum", time: "By appointment", imageUrl: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQIKGa6oaaAOoX9iOGULDpYJM3zV6MFgQOK_TxIB-FCJzAa4yALZWXcNTie&s=10" },
    { id: "guiguinto-malolos-arch", municipality: "Guiguinto", name: "Guiguinto–Malolos Boundary Welcome Arch", description: "A roadside arch marking historic town limits and a photo stop for travelers.", location: "Manila North Rd, Guiguinto, Bulacan", category: "Heritage", tag: "Roadside", time: "Quick stop", imageUrl: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTtBdzOYEnAuTckr7NDMy1U6a4j-n8n6G-KN_Trk6CTTMhQgnqKct_1XFdP&s=10" },
    { id: "inang-filipinas-shrine", municipality: "Pandi", name: "Inang Filipinas Shrine", description: "A small shrine with civic and historical symbolism for the local community.", location: "Cacarong De Real, Pandi, Bulacan", category: "Heritage", tag: "Shrine", time: "Short visit", imageUrl: "https://d3fphkxyf5o5bm.cloudfront.net/image-resize/format=webp,w=1200/QwRY54Li1HMwD7oNfqLxPEF5e2p1dvQLXUa7YjY2Zq" },
    { id: "angat-dam", municipality: "Norzagaray", name: "Angat Dam", description: "A major engineering landmark with viewing points across the reservoir.", location: "Norzagaray, Bulacan", category: "Heritage", tag: "Reservoir view", time: "Short visit", imageUrl: "https://media.philstar.com/photos/2023/07/02/5_2023-07-02_23-29-31.jpg" },

    // Churches & Shrines
    { id: "barasoain-church", municipality: "Malolos", name: "Barasoain Church (Our Lady of Mount Carmel Parish)", description: "A stone church of paramount historic importance and fine colonial architecture.", location: "Paseo del Congreso cor. Don Antonio Bautista St, Malolos, 3000 Bulacan", category: "Sacred", tag: "Historic church", time: "30–60 minutes", imageUrl: "https://as2.ftcdn.net/jpg/02/66/96/15/1000_F_266961583_7vsjOxLaD9wre1dbEsmZYX4YktptOo2M.jpg" },
    { id: "malolos-cathedral", municipality: "Malolos", name: "Minor Basilica and Cathedral of the Immaculate Conception (Malolos Cathedral)", description: "A stately cathedral and center of religious life in the city.", location: "Malolos, Bulacan", category: "Sacred", tag: "Basilica", time: "Short visit", imageUrl: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSGubnnx8VNIDweglpbw_avL6z_Pnw3inTwa3pT0UZMBe3Y3fasw-hZ_wA&s=10" },
    { id: "divine-mercy-shrine", municipality: "Marilao", name: "National Shrine and Parish of the Divine Mercy", description: "A modern pilgrimage site and a quiet place for reflection.", location: "Sta. Rosa I, Marilao, 3019 Bulacan", category: "Sacred", tag: "Pilgrimage", time: "One hour", imageUrl: "https://dynamic-media-cdn.tripadvisor.com/media/photo-o/0f/07/97/e9/national-shrine-of-the.jpg?w=1200&h=-1&s=1" },
    { id: "baliwag-church", municipality: "Baliwag", name: "Diocesan Shrine and Parish of St. Augustine (Baliuag Church)", description: "A landmark parish church known for civic and religious events.", location: "Benigno S. Aquino Ave, Baliwag, Bulacan", category: "Sacred", tag: "Parish", time: "Short visit", imageUrl: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS0DDaDf--gVlA5a6qzRNqeuoVa9iNstu0vaoKr9xxqI7EYGW-ZbzTUiWoL&s=10" },
    { id: "calumpit-church", municipality: "Calumpit", name: "Diocesan Shrine and Parish of St. John the Baptist (Calumpit Church)", description: "An old parish in Calumpit with layered colonial details.", location: "Poblacion Rd, Calumpit, 3003 Bulacan", category: "Sacred", tag: "Colonial", time: "Quick stop", imageUrl: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTabNLTGSO8Sjp7A8ayzFVTTNOOVXSs0w-1rpX_EU2uYOKqZvwut3OKjb4&s=10" },
    { id: "krus-sa-wawa", municipality: "Bocaue", name: "St. Martin of Tours Parish – Shrine of Krus sa Wawa", description: "A parish with an evocative shrine and riverside setting, home each July to the Bocaue River Festival procession.", location: "Bocaue Town Proper, Bocaue, 3018 Bulacan", category: "Sacred", tag: "Riverside shrine", time: "Short visit", imageUrl: "https://upload.wikimedia.org/wikipedia/commons/6/68/8125Saint_Martin_of_Tours_Parish_Holy_Cross_Shrine_Bulacan_03.jpg?utm_source=commons.wikimedia.org&utm_campaign=index&utm_content=original" },
    { id: "padre-pio-mountain", municipality: "SJDM", name: "Padre Pio Mountain of Healing", description: "A mountain-side retreat with a devotional atmosphere and scenic access routes.", location: "Area C, Brgy. Paradise, SJDM, 3023 Bulacan", category: "Sacred", tag: "Retreat", time: "Morning", imageUrl: "https://files01.pna.gov.ph/source/2023/04/06/padre-pio-mt.jpg" },

    // Resorts & Waterparks
    { id: "klir-waterpark", municipality: "Guiguinto", name: "Klir Waterpark Resort and Hotel", description: "A family waterpark and hotel complex with wave pools and slides.", location: "near Sta. Rita Exit, Kabilang Bakood, Guiguinto, Bulacan", category: "Resort", tag: "Waterpark", time: "Day pass", imageUrl: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRRpv6_Sa1GVakYi06mM-QyVJMGQjjO9qXx89FjnUvUUQ&s=10" },
    { id: "hacienda-angelita", municipality: "San Rafael", name: "Hacienda Angelita Nature Farm and Resort", description: "An agricultural resort, farm café, and relaxing grounds.", location: "268 Balubaran St, Capihan, San Rafael, 3008 Bulacan", category: "Resort", tag: "Farm stay", time: "Half-day or overnight", imageUrl: "https://dynamic-media-cdn.tripadvisor.com/media/photo-o/2b/6d/c8/5d/our-deluxe-accommodations.jpg?w=900&h=-1&s=1" },
    { id: "adventure-resort", municipality: "Norzagaray", name: "Adventure Resort", description: "A riverside resort offering rafting and family activities.", location: "Norzagaray-San Jose Rd, Norzagaray, Bulacan", category: "Resort", tag: "Adventure", time: "Half-day", imageUrl: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSlvV-sI2EBc9O0eNJjahV35hShL9PR6IV-L2DRwKQMJo7UeZSsgPogIjM&s=10" },
    { id: "d-north-riverside", municipality: "Meycauayan", name: "D North Riverside Resort and Waterpark", description: "A sizable waterpark and function-resort outside the city.", location: "305 Cordero, Langka, Meycauayan, 3020 Bulacan", category: "Resort", tag: "Family", time: "Day pass", imageUrl: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQXq5_K59c2_5Gi6VaMYZKooeP0S_7j12v2EdYxd2IElWmKj_Q_nFdaMV0&s=10" },
    { id: "cool-waves", municipality: "Bulakan", name: "Cool Waves Bulacan Waterpark Resort", description: "A polished waterpark destination with multiple pools and slides.", location: "777 Libo St, San Nicolas, Bulakan, 3017 Bulacan", category: "Resort", tag: "Waterpark", time: "Family day", imageUrl: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSAhO-9bVBpUwkbLpYO8vPIDnsxA0wSLx7-hEhCli_mc5C7IzgAGoc2Lco&s=10" },
    { id: "amana-waterpark", municipality: "Pandi", name: "Amana Waterpark", description: "An accessible waterpark hosting families and weekend groups.", location: "Bagong Barrio, Santisima St, Pandi, Bulacan", category: "Resort", tag: "Wave pools", time: "Day pass", imageUrl: "https://jayetravels.com/wp-content/uploads/2025/03/Home-1000x600.jpg" },
    { id: "malamig-park", municipality: "Bustos", name: "Malamig Park Resort", description: "A relaxing resort with native cottages and pool facilities.", location: "Brgy. Malamig, Bustos, 3007 Bulacan", category: "Resort", tag: "Resort", time: "Day or overnight", imageUrl: "https://static.wixstatic.com/media/9b0063_1dc6246117ae47dfb621333ecd3fec86~mv2.png/v1/fill/w_980,h_689,al_c,q_90,usm_0.66_1.00_0.01,enc_avif,quality_auto/9b0063_1dc6246117ae47dfb621333ecd3fec86~mv2.png" },
    { id: "san-rafael-river-adventure", municipality: "San Rafael", name: "San Rafael River Adventure", description: "Glamping and river activities in a nature-forward resort setting.", location: "Brgy. Talacsan Rd, San Rafael, 3008 Bulacan", category: "Resort", tag: "Glamping", time: "Overnight worthy", imageUrl: "https://sanrafaelriveradventure.com/wp-content/uploads/2023/08/WhatsApp-Image-2023-08-09-at-11.27.22-1.jpg" },
    { id: "la-florentina", municipality: "Bustos", name: "La Florentina Resort", description: "A private resort with pool areas and event facilities.", location: "875 Claro Santos, Bonga Menor, Bustos, 3007 Bulacan", category: "Resort", tag: "Private pool", time: "Day pass", imageUrl: "https://images.trvl-media.com/lodging/44000000/43980000/43979400/43979364/5466f954.jpg?impolicy=resizecrop&rw=575&rh=575&ra=fill" },
    { id: "grotto-vista", municipality: "SJDM", name: "Grotto Vista Resort", description: "A hillside resort space with small-scale cabins and pools.", location: "Graceville, SJDM, 3023 Bulacan", category: "Resort", tag: "Small resort", time: "Half-day", imageUrl: "https://tanglawan.ph/wp-content/uploads/2022/04/B1-scaled.jpg" },
    { id: "pulong-kabyawan", municipality: "Pulilan", name: "Pulong Kabyawan (farm café)", description: "A farm café blending local produce with relaxed garden seating.", location: "Inaon, Pulilan, Bulacan", category: "Resort", tag: "Farm café", time: "Afternoon", imageUrl: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRqkkdIk8OYYaP2YUXlWqNNLINjpN_-61AgSDIiqjTz6CrTOf9uXjOTkao&s=10" }
];

/* -------------------------------------------------------------------
   Destination rating helpers
   Real average rating + review count, loaded from the database via
   api/get_ratings.php and cached here for both the home page and the
   full guide to read from. A place with no reviews yet is "New", not
   a made-up number.
   ------------------------------------------------------------------- */

let ratingsCache = {};

/**
 * Inserts a new place, or updates an existing one in place (so any
 * page currently rendering the array picks up the edit on next
 * render), keyed by slug.
 */
function applyDestinationUpdate(place) {
    const index = destinations.findIndex(
        (existing) => existing.id === place.id
    );

    if (index === -1) {
        destinations.push(place);
    } else {
        destinations[index] = place;
    }
}

/**
 * Places added or edited through the admin panel live in the
 * `destinations` DB table; the 40 built-in places live in the array
 * above. Rows here overwrite a built-in place with the same slug
 * (so admin edits actually show up) and any new slug is appended.
 */
async function syncDestinationsFromServer() {
    try {
        const res = await fetch("api/get_destinations.php");
        const data = await res.json();

        if (!data.ok) {
            return;
        }

        data.destinations.forEach(applyDestinationUpdate);
    } catch {
        // Offline or unreachable — the guide still shows its built-in list.
    }
}

async function syncRatingsFromServer() {
    try {
        const res = await fetch("api/get_ratings.php");
        const data = await res.json();

        if (data.ok) {
            ratingsCache = data.ratings;
        }
    } catch {
        // Couldn't reach the database — destinations render as "New"
        // rather than showing a stale or fabricated number.
    }
}

function ratingFor(place) {
    const entry = ratingsCache[place.id];

    if (!entry || !entry.count) {
        return { rating: "New", reviews: 0 };
    }

    return { rating: entry.rating.toFixed(1), reviews: entry.count };
}

function starString(rating) {
    const parsed = Number(rating);
    const full = Number.isFinite(parsed) ? Math.round(parsed) : 0;

    return "★".repeat(full) + "☆".repeat(5 - full);
}

function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, (character) => ({
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#039;"
    }[character]));
}

function findDestination(id) {
    return destinations.find((place) => place.id === id) || null;
}

/* -------------------------------------------------------------------
   Seed reviews
   ------------------------------------------------------------------- */

const seedReviews = {
    "barasoain-church": [
        {
            name: "Maria Santos",
            initials: "MS",
            date: "March 2025",
            rating: 5,
            text: "Truly magnificent. The architecture is breathtaking and the history embedded in its walls is palpable. A must for every Filipino."
        },
        {
            name: "Jose Reyes",
            initials: "JR",
            date: "January 2025",
            rating: 5,
            text: "Humbling experience. You can feel the weight of history standing inside. The guide was knowledgeable and passionate."
        }
    ],

    "mount-manalmon": [
        {
            name: "Paolo Guerrero",
            initials: "PG",
            date: "February 2025",
            rating: 5,
            text: "The ridge views near the summit are worth every step. Bring more water than you think you need."
        }
    ],

    "krus-sa-wawa": [
        {
            name: "Elena Pascual",
            initials: "EP",
            date: "July 2025",
            rating: 5,
            text: "Caught the river festival procession from here — the lit-up pagodas on the water are unlike anything else I've seen."
        }
    ]
};

/* ===================================================================
   AUTH PAGE
   =================================================================== */

/*
 * Switch between Login and Sign Up.
 */
function switchAuthTab(tab) {
    const loginTab = document.getElementById("loginTab");
    const signupTab = document.getElementById("signupTab");

    const loginForm = document.getElementById("loginForm");
    const signupForm = document.getElementById("signupForm");

    if (!loginTab || !signupTab || !loginForm || !signupForm) {
        return;
    }

    if (tab === "login") {
        loginTab.classList.add("active");
        signupTab.classList.remove("active");

        loginForm.style.display = "block";
        signupForm.style.display = "none";
    } else {
        signupTab.classList.add("active");
        loginTab.classList.remove("active");

        signupForm.style.display = "block";
        loginForm.style.display = "none";
    }
}


/*
 * Show / hide Login password.
 */
function togglePassword(button) {
    const input = document.getElementById("passwordInput");

    if (!input || !button) {
        return;
    }

    const showing = input.type === "text";

    input.type = showing ? "password" : "text";
    button.textContent = showing ? "Show" : "Hide";
}


/*
 * Show / hide Sign Up password.
 */
function toggleSignupPassword(button) {
    const input = document.getElementById("signupPasswordInput");

    if (!input || !button) {
        return;
    }

    const showing = input.type === "text";

    input.type = showing ? "password" : "text";
    button.textContent = showing ? "Show" : "Hide";
}


/*
 * IMPORTANT:
 *
 * PHP handles authentication.
 *
 * The OLD version was:
 *
 * function handleAuthSubmit(event) {
 *     event.preventDefault();
 *     window.location.href = "userprofile.php";
 * }
 *
 * That was causing the problem because JavaScript stopped the form
 * from being submitted to login.php/signup.php.
 *
 * This function now allows the normal form submission to continue.
 *
 * You can also remove this function completely if you remove
 * onsubmit="handleAuthSubmit(event)" from auth.php.
 */
function handleAuthSubmit(event) {
    return true;
}


/* ===================================================================
   HOME PAGE
   =================================================================== */

function initHomePage() {
    const grid = document.querySelector("#featured-grid");

    if (!grid) {
        return;
    }

    const featuredIds = [
        "barasoain-church",
        "mount-manalmon",
        "krus-sa-wawa"
    ];

    const favorites = readFavorites();

    grid.innerHTML = featuredIds.map((id, index) => {
        const place = findDestination(id);

        if (!place) {
            return "";
        }

        const { rating, reviews } = ratingFor(place);
        const isFavorite = favorites.includes(place.id);

        return `
        <article class="destination-card" style="animation-delay:${index * .06}s">

            <a
                class="card-media image-frame"
                href="destination.php?place=${encodeURIComponent(place.id)}#places"
                aria-label="View ${escapeHtml(place.name)}"
            >
                <img
                    src="${escapeHtml(place.imageUrl || "")}"
                    alt="${escapeHtml(place.name)} in ${escapeHtml(place.municipality)}"
                    loading="lazy"
                />

                <span class="image-fallback" aria-hidden="true">◉</span>

                <span class="card-badge category-label">
                    ${escapeHtml(place.category)}
                </span>

                <span class="card-badge rating-badge">
                    <span class="star" aria-hidden="true">★</span>
                    ${rating}
                </span>
            </a>

            <div class="card-content">

                <div class="place-municipality">
                    <span aria-hidden="true">⌖</span>
                    ${escapeHtml(place.municipality)}
                </div>

                <div class="card-copy">

                    <h3>${escapeHtml(place.name)}</h3>

                    <div class="card-stars">
                        <span aria-hidden="true">
                            ${starString(rating)}
                        </span>

                        <span class="count">
                            (${reviews} reviews)
                        </span>
                    </div>

                    <p>
                        ${escapeHtml(place.description)}
                    </p>

                </div>

                <a
                    class="read-note"
                    href="destination.php?place=${encodeURIComponent(place.id)}#places"
                >
                    Read more &amp; reviews
                    <span aria-hidden="true">↗</span>
                </a>

            </div>

        </article>`;
    }).join("");

    wireImageFallbacks();
    updateCounts();
}


/* ===================================================================
   DESTINATION PAGE
   =================================================================== */

function initDestinationPage() {
    const grid = document.querySelector("#destination-grid");

    if (!grid) {
        return;
    }

    const municipalities = [
        "All municipalities",
        ...new Set(destinations.map((place) => place.municipality))
    ];

    const categories = [
        "All moods",
        ...new Set(destinations.map((place) => place.category))
    ];

    const state = {
        query: "",
        municipality: municipalities[0],
        category: categories[0],
        showSaved: false,
        favorites: readFavorites(),
        reviews: readReviews(),
        reviewRating: 5,
        selected: null,
        myReview: null
    };

    function readReviews() {
        try {
            return JSON.parse(
                localStorage.getItem("travelbuddies-reviews") || "{}"
            );
        } catch {
            return {};
        }
    }

    function writeReviews() {
        localStorage.setItem(
            "travelbuddies-reviews",
            JSON.stringify(state.reviews)
        );
    }

    function getReviewsFor(placeId) {
        if (state.reviews[placeId]) {
            return state.reviews[placeId];
        }

        return seedReviews[placeId] || [];
    }

    const elements = {
        grid,
        empty: document.querySelector("#empty-state"),
        search: document.querySelector("#search-input"),
        municipality: document.querySelector("#municipality-select"),
        category: document.querySelector("#category-select"),
        chips: document.querySelector("#category-chips"),
        savedFilter: document.querySelector("#saved-filter"),
        clear: document.querySelector("#clear-filters"),
        results: document.querySelector("#results-count"),
        modal: document.querySelector("#detail-modal"),
        modalImage: document.querySelector("#modal-image"),
        modalKicker: document.querySelector("#modal-kicker"),
        modalTitle: document.querySelector("#modal-title"),
        modalMunicipality: document.querySelector("#modal-municipality"),
        modalRating: document.querySelector("#modal-rating"),
        modalDescription: document.querySelector("#modal-description"),
        modalTags: document.querySelector("#modal-tags"),
        modalLocation: document.querySelector("#modal-location"),
        modalTime: document.querySelector("#modal-time"),
        modalFavorite: document.querySelector("#modal-favorite"),
        modalShare: document.querySelector("#modal-share"),
        shareLabel: document.querySelector("#share-label"),
        writeReviewBtn: document.querySelector("#write-review-btn"),
        reviewForm: document.querySelector("#review-form"),
        reviewFormHint: document.querySelector("#review-form-hint"),
        starInput: document.querySelector("#star-input"),
        reviewText: document.querySelector("#review-text"),
        cancelReviewBtn: document.querySelector("#cancel-review-btn"),
        submitReviewBtn: document.querySelector("#submit-review-btn"),
        reviewList: document.querySelector("#review-list"),
        adminAddBtn: document.querySelector("#admin-add-btn"),
        modalAdminEdit: document.querySelector("#modal-admin-edit"),
        adminModal: document.querySelector("#admin-edit-modal"),
        adminForm: document.querySelector("#admin-edit-form"),
        adminClose: document.querySelector("#admin-edit-close"),
        adminTitle: document.querySelector("#admin-edit-title"),
        adminOriginalSlug: document.querySelector("#admin-field-original-slug"),
        adminName: document.querySelector("#admin-field-name"),
        adminSlug: document.querySelector("#admin-field-slug"),
        adminMunicipality: document.querySelector("#admin-field-municipality"),
        adminCategory: document.querySelector("#admin-field-category"),
        adminTag: document.querySelector("#admin-field-tag"),
        adminFieldNote: document.querySelector("#admin-field-note"),
        adminLocation: document.querySelector("#admin-field-location"),
        adminImageUrl: document.querySelector("#admin-field-image-url"),
        adminImageFile: document.querySelector("#admin-field-image-file"),
        adminDescription: document.querySelector("#admin-field-description"),
        adminError: document.querySelector("#admin-form-error"),
        adminDeleteBtn: document.querySelector("#admin-delete-btn")
    };

    function writeFavorites() {
        localStorage.setItem(
            "travelbuddies-favorites",
            JSON.stringify(state.favorites)
        );
    }

    function populateFilters() {
        elements.municipality.innerHTML = municipalities
            .map(
                (town) =>
                    `<option>${escapeHtml(town)}</option>`
            )
            .join("");

        elements.category.innerHTML = categories
            .map(
                (item) =>
                    `<option>${escapeHtml(item)}</option>`
            )
            .join("");

        elements.chips.innerHTML = categories
            .slice(1)
            .map(
                (item) => `
                    <button
                        class="filter-chip ${
                            state.category === item ? "is-active" : ""
                        }"
                        type="button"
                        data-category="${escapeHtml(item)}"
                    >
                        ${escapeHtml(item)}
                    </button>
                `
            )
            .join("");
    }

    function getVisibleDestinations() {
        const normalized = state.query.trim().toLowerCase();

        return destinations.filter((place) => {

            const matchesQuery =
                !normalized ||
                [
                    place.name,
                    place.municipality,
                    place.description,
                    place.category
                ]
                    .join(" ")
                    .toLowerCase()
                    .includes(normalized);

            const matchesMunicipality =
                state.municipality === municipalities[0] ||
                place.municipality === state.municipality;

            const matchesCategory =
                state.category === categories[0] ||
                place.category === state.category;

            const matchesSaved =
                !state.showSaved ||
                state.favorites.includes(place.id);

            return (
                matchesQuery &&
                matchesMunicipality &&
                matchesCategory &&
                matchesSaved
            );
        });
    }

    function renderCards() {
        const visible = getVisibleDestinations();

        elements.grid.innerHTML = visible
            .map((place, index) => {

                const isFavorite =
                    state.favorites.includes(place.id);

                const { rating, reviews } =
                    ratingFor(place);

                return `
                <article
                    class="destination-card"
                    style="animation-delay:${Math.min(
                        index * .035,
                        .45
                    )}s"
                >

                    <div class="card-media image-frame">

                        <img
                            src="${escapeHtml(place.imageUrl || "")}"
                            alt="${escapeHtml(place.name)} in ${escapeHtml(place.municipality)}"
                            loading="lazy"
                        />

                        <span
                            class="image-fallback"
                            aria-hidden="true"
                        >
                            ◉
                        </span>

                        <span class="card-badge category-label">
                            ${escapeHtml(place.category)}
                        </span>

                        <span class="card-badge rating-badge">
                            <span class="star" aria-hidden="true">★</span>
                            ${rating}
                        </span>

                        <button
                            class="favorite-button ${
                                isFavorite ? "is-favorite" : ""
                            }"
                            type="button"
                            data-favorite="${place.id}"
                            aria-label="${
                                isFavorite ? "Remove" : "Save"
                            } ${escapeHtml(place.name)}"
                        >
                            ${isFavorite ? "✓" : "♡"}
                        </button>

                    </div>

                    <div class="card-content">

                        <div class="place-municipality">
                            <span aria-hidden="true">⌖</span>
                            ${escapeHtml(place.municipality)}
                        </div>

                        <div class="card-copy">

                            <h3>
                                ${escapeHtml(place.name)}
                            </h3>

                            <div class="card-stars">
                                <span aria-hidden="true">
                                    ${starString(rating)}
                                </span>

                                <span class="count">
                                    (${reviews} reviews)
                                </span>
                            </div>

                            <p>
                                ${escapeHtml(place.description)}
                            </p>

                        </div>

                        <div class="card-tags">

                            <span class="card-tag">
                                ${escapeHtml(place.category)}
                            </span>

                            <span class="card-tag">
                                ${escapeHtml(
                                    place.tag ||
                                    place.time ||
                                    ""
                                )}
                            </span>

                        </div>

                        <button
                            class="read-note"
                            type="button"
                            data-open="${place.id}"
                        >
                            View Details &amp; Reviews
                            <span aria-hidden="true">↗</span>
                        </button>

                    </div>

                </article>`;
            })
            .join("");

        elements.empty.classList.toggle(
            "hidden",
            visible.length > 0
        );

        elements.grid.classList.toggle(
            "hidden",
            visible.length === 0
        );

        const locationLabel = state.showSaved
            ? "your saved places"
            : state.municipality === municipalities[0]
                ? "all municipalities"
                : state.municipality;

        elements.results.textContent =
            `${String(visible.length).padStart(2, "0")} entries / ${locationLabel}`;

        elements.savedFilter.classList.toggle(
            "is-active",
            state.showSaved
        );

        elements.clear.classList.toggle(
            "hidden",
            !(
                state.query ||
                state.municipality !== municipalities[0] ||
                state.category !== categories[0] ||
                state.showSaved
            )
        );

        elements.chips
            .querySelectorAll("[data-category]")
            .forEach((chip) => {
                chip.classList.toggle(
                    "is-active",
                    chip.dataset.category === state.category
                );
            });

        updateCounts();
        wireImageFallbacks();
    }

    function toggleFavorite(id) {
        if (!window.APP_CONFIG || !window.APP_CONFIG.loggedIn) {
            window.location.href = "auth.php";
            return;
        }

        const wasFavorite = state.favorites.includes(id);

        state.favorites = wasFavorite
            ? state.favorites.filter(
                (item) => item !== id
            )
            : [...state.favorites, id];

        writeFavorites();
        renderCards();

        if (
            state.selected &&
            state.selected.id === id
        ) {
            renderModal(state.selected);
        }

        fetch("api/toggle_favorite.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                slug: id,
                csrf_token: window.APP_CONFIG.csrfToken
            })
        })
            .then((res) => res.json())
            .then((data) => {
                if (!data.ok) {
                    throw new Error(data.error || "Save failed");
                }
            })
            .catch(() => {
                // Couldn't save to the account — undo the optimistic
                // update so the UI doesn't claim it's saved when it isn't.
                state.favorites = wasFavorite
                    ? [...state.favorites, id]
                    : state.favorites.filter((item) => item !== id);

                writeFavorites();
                renderCards();

                if (
                    state.selected &&
                    state.selected.id === id
                ) {
                    renderModal(state.selected);
                }
            });
    }

    function clearFilters() {
        state.query = "";
        state.municipality = municipalities[0];
        state.category = categories[0];
        state.showSaved = false;

        elements.search.value = "";
        elements.municipality.value =
            state.municipality;
        elements.category.value =
            state.category;

        populateFilters();
        renderCards();
    }

    function renderStarInput() {
        if (!elements.starInput) {
            return;
        }

        elements.starInput.innerHTML =
            [1, 2, 3, 4, 5]
                .map(
                    (value) => `
                    <span
                        class="star ${
                            value <= state.reviewRating
                                ? "is-active"
                                : ""
                        }"
                        data-star="${value}"
                        role="radio"
                        aria-checked="${
                            value === state.reviewRating
                        }"
                        tabindex="0"
                    >
                        ★
                    </span>
                `
                )
                .join("");
    }

    function resetReviewForm() {
        state.reviewRating = 5;

        if (elements.reviewText) {
            elements.reviewText.value = "";
        }

        if (elements.reviewForm) {
            elements.reviewForm.classList.add("hidden");
        }

        renderStarInput();
    }

    function renderReviews(place) {
        if (!elements.reviewList) {
            return;
        }

        // Unknown until the fetch resolves — don't carry over the
        // previous place's "you already reviewed this" state.
        state.myReview = null;
        updateReviewButtonLabels();

        // Paint immediately from local/seed data so the modal never
        // looks empty while the network request is in flight, then
        // replace it with the real database reviews once they load.
        paintReviews(getReviewsFor(place.id));

        fetch(
            `api/get_reviews.php?slug=${encodeURIComponent(place.id)}`
        )
            .then((res) => res.json())
            .then((data) => {
                if (!data.ok || !state.selected || state.selected.id !== place.id) {
                    return;
                }

                // Only replace the placeholder if the database actually
                // has reviews — an empty result usually just means the
                // destinations table hasn't been seeded for this place yet.
                if (data.reviews.length > 0) {
                    paintReviews(data.reviews);
                }

                state.myReview = data.mine || null;
                updateReviewButtonLabels();
            })
            .catch(() => {
                // Database not reachable — keep showing the local/seed list.
            });
    }

    /**
     * Makes it visible, rather than a surprise, that submitting again
     * edits the review already on file instead of creating a new one.
     */
    function updateReviewButtonLabels() {
        if (elements.writeReviewBtn) {
            elements.writeReviewBtn.textContent = state.myReview
                ? "✎ Edit Your Review"
                : "+ Write a Review";
        }

        if (elements.submitReviewBtn) {
            elements.submitReviewBtn.textContent = state.myReview
                ? "Update Review"
                : "Submit Review";
        }

        elements.reviewFormHint?.classList.toggle(
            "hidden",
            !state.myReview
        );
    }

    function paintReviews(list) {
        if (!elements.reviewList) {
            return;
        }

        if (list.length === 0) {
            elements.reviewList.innerHTML = `
                <p class="review-empty">
                    No reviews yet — be the first to share your experience.
                </p>
            `;

            return;
        }

        elements.reviewList.innerHTML = list
            .map(
                (review) => `
                <article class="review-card">

                    <div class="review-top">

                        <div class="review-who">

                            <span class="review-avatar">
                                ${escapeHtml(review.initials)}
                            </span>

                            <div>

                                <div class="review-name">
                                    ${escapeHtml(review.name)}
                                </div>

                                <div class="review-date">
                                    ${escapeHtml(review.date)}
                                </div>

                            </div>

                        </div>

                        <div
                            class="review-stars"
                            aria-hidden="true"
                        >
                            ${starString(review.rating)}
                        </div>

                    </div>

                    <p class="review-text">
                        ${escapeHtml(review.text)}
                    </p>

                </article>
            `
            )
            .join("");
    }

    function submitReview() {
        if (!window.APP_CONFIG || !window.APP_CONFIG.loggedIn) {
            window.location.href = "auth.php";
            return;
        }

        if (!state.selected) {
            return;
        }

        const text =
            (elements.reviewText?.value || "").trim();

        if (!text) {
            elements.reviewText?.focus();
            return;
        }

        submitReviewToServer(state.selected.id, text);
    }

    function submitReviewToServer(placeId, text) {
        fetch("api/submit_review.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                slug: placeId,
                rating: state.reviewRating,
                comment: text,
                csrf_token: window.APP_CONFIG.csrfToken
            })
        })
            .then((res) => res.json())
            .then((data) => {
                if (!data.ok) {
                    throw new Error(data.error || "Save failed");
                }

                renderReviews(state.selected);
                resetReviewForm();

                return syncRatingsFromServer();
            })
            .then(() => {
                renderCards();

                if (
                    elements.modalRating &&
                    state.selected &&
                    state.selected.id === placeId
                ) {
                    const { rating, reviews } = ratingFor(state.selected);

                    elements.modalRating.innerHTML = `
                        <span aria-hidden="true">${starString(rating)}</span>
                        <span class="count">${rating} (${reviews} reviews)</span>
                    `;
                }
            })
            .catch(() => {
                alert(
                    "Couldn't save your review right now — please try again."
                );
            });
    }

    function renderModal(place) {
        state.selected = place;

        const { rating, reviews } =
            ratingFor(place);

        elements.modalImage.src =
            place.imageUrl || "";

        elements.modalImage.alt =
            `${place.name} in ${place.municipality}`;

        elements.modalKicker.textContent =
            `${place.category} / ${place.tag || ""}`;

        elements.modalTitle.textContent =
            place.name;

        elements.modalMunicipality.textContent =
            place.municipality;

        if (elements.modalRating) {
            elements.modalRating.innerHTML = `
                <span aria-hidden="true">
                    ${starString(rating)}
                </span>

                <span class="count">
                    ${rating} (${reviews} reviews)
                </span>
            `;
        }

        elements.modalDescription.textContent =
            place.description;

        if (elements.modalTags) {
            elements.modalTags.innerHTML = `
                <span class="card-tag">
                    ${escapeHtml(place.category)}
                </span>

                <span class="card-tag">
                    ${escapeHtml(
                        place.tag ||
                        place.time ||
                        ""
                    )}
                </span>
            `;
        }

        elements.modalLocation.textContent =
            place.location;

        elements.modalTime.textContent =
            place.time || "";

        const isFavorite =
            state.favorites.includes(place.id);

        elements.modalFavorite.innerHTML = `
            ${isFavorite ? "✓" : "♡"}
            ${
                isFavorite
                    ? "Saved to your route"
                    : "Save this place"
            }
        `;

        elements.shareLabel.textContent =
            "Share the note";

        resetReviewForm();
        renderReviews(place);

        elements.modal.classList.remove("hidden");

        document.body.style.overflow =
            "hidden";

        elements.modalImage.parentElement
            .classList.remove("has-failed");

        elements.modalImage.addEventListener(
            "error",
            () =>
                elements.modalImage.parentElement
                    .classList.add("has-failed"),
            {
                once: true
            }
        );
    }

    function closeModal() {
        state.selected = null;

        elements.modal.classList.add("hidden");

        document.body.style.overflow = "";

        if (
            window.location.search.includes(
                "place="
            )
        ) {
            const url =
                new URL(window.location.href);

            url.searchParams.delete("place");

            window.history.replaceState(
                {},
                "",
                url.pathname + url.hash
            );
        }
    }

    function sharePlace(place) {
        const shareText =
            `${place.name} in ${place.municipality} — TravelBuddies Bulacan`;

        const finish = () => {
            elements.shareLabel.textContent =
                "Copied to clipboard";

            window.setTimeout(() => {
                if (!state.selected) {
                    return;
                }

                elements.shareLabel.textContent =
                    "Share the note";
            }, 2200);
        };

        if (
            navigator.clipboard &&
            navigator.clipboard.writeText
        ) {
            navigator.clipboard
                .writeText(shareText)
                .then(finish)
                .catch(finish);
        } else {
            finish();
        }
    }

    /* ---------------------------------------------------------------
       Destination event listeners
       --------------------------------------------------------------- */

    elements.search.addEventListener(
        "input",
        (event) => {
            state.query =
                event.target.value;

            renderCards();
        }
    );

    elements.municipality.addEventListener(
        "change",
        (event) => {
            state.municipality =
                event.target.value;

            renderCards();
        }
    );

    elements.category.addEventListener(
        "change",
        (event) => {
            state.category =
                event.target.value;

            populateFilters();

            elements.municipality.value =
                state.municipality;

            elements.category.value =
                state.category;

            renderCards();
        }
    );

    elements.chips.addEventListener(
        "click",
        (event) => {
            const chip =
                event.target.closest(
                    "[data-category]"
                );

            if (!chip) {
                return;
            }

            state.category =
                state.category ===
                chip.dataset.category
                    ? categories[0]
                    : chip.dataset.category;

            elements.category.value =
                state.category;

            populateFilters();

            elements.municipality.value =
                state.municipality;

            elements.category.value =
                state.category;

            renderCards();
        }
    );

    elements.savedFilter.addEventListener(
        "click",
        () => {
            state.showSaved =
                !state.showSaved;

            renderCards();
        }
    );

    document
        .querySelectorAll(
            "#nav-saved, #mobile-saved"
        )
        .forEach((button) => {
            button.addEventListener(
                "click",
                () => {
                    state.showSaved =
                        !state.showSaved;

                    document
                        .querySelector("#places")
                        ?.scrollIntoView({
                            behavior: "smooth"
                        });

                    renderCards();
                }
            );
        });

    elements.clear.addEventListener(
        "click",
        clearFilters
    );

    document
        .querySelector("#empty-clear")
        ?.addEventListener(
            "click",
            clearFilters
        );

    document
        .querySelector("#brand-home")
        ?.addEventListener(
            "click",
            clearFilters
        );

    elements.grid.addEventListener(
        "click",
        (event) => {
            const favorite =
                event.target.closest(
                    "[data-favorite]"
                );

            const opener =
                event.target.closest(
                    "[data-open]"
                );

            if (favorite) {
                toggleFavorite(
                    favorite.dataset.favorite
                );
            }

            if (opener) {
                renderModal(
                    destinations.find(
                        (place) =>
                            place.id ===
                            opener.dataset.open
                    )
                );
            }
        }
    );

    document
        .querySelector("#close-modal")
        ?.addEventListener(
            "click",
            closeModal
        );

    elements.modal?.addEventListener(
        "mousedown",
        (event) => {
            if (
                event.target ===
                elements.modal
            ) {
                closeModal();
            }
        }
    );

    document.addEventListener(
        "keydown",
        (event) => {
            if (
                event.key === "Escape" &&
                elements.modal &&
                !elements.modal.classList.contains(
                    "hidden"
                )
            ) {
                closeModal();
            }
        }
    );

    elements.modalFavorite?.addEventListener(
        "click",
        () => {
            if (state.selected) {
                toggleFavorite(
                    state.selected.id
                );
            }
        }
    );

    elements.modalShare?.addEventListener(
        "click",
        () => {
            if (state.selected) {
                sharePlace(
                    state.selected
                );
            }
        }
    );

    /* ---------------------------------------------------------------
       Admin: add / edit / delete a place
       --------------------------------------------------------------- */

    function openAdminEditModal(place) {
        if (!elements.adminModal) {
            return;
        }

        elements.adminError?.classList.add("hidden");

        if (place) {
            elements.adminTitle.textContent = "Edit place";
            elements.adminOriginalSlug.value = place.id;
            elements.adminName.value = place.name || "";
            elements.adminSlug.value = place.id || "";
            elements.adminMunicipality.value = place.municipality || "";
            elements.adminCategory.value = place.category || "";
            elements.adminTag.value = place.tag || "";
            elements.adminFieldNote.value = place.time || "";
            elements.adminLocation.value = place.location || "";
            elements.adminImageUrl.value = place.imageUrl || "";
            elements.adminDescription.value = place.description || "";
            elements.adminDeleteBtn?.classList.remove("hidden");
        } else {
            elements.adminTitle.textContent = "Add a new place";
            elements.adminForm?.reset();
            elements.adminOriginalSlug.value = "";
            elements.adminDeleteBtn?.classList.add("hidden");
        }

        elements.adminModal.classList.remove("hidden");
    }

    function closeAdminEditModal() {
        elements.adminModal?.classList.add("hidden");
    }

    elements.adminAddBtn?.addEventListener(
        "click",
        () => openAdminEditModal(null)
    );

    elements.modalAdminEdit?.addEventListener(
        "click",
        () => {
            if (state.selected) {
                openAdminEditModal(state.selected);
            }
        }
    );

    elements.adminClose?.addEventListener(
        "click",
        closeAdminEditModal
    );

    elements.adminModal?.addEventListener(
        "click",
        (event) => {
            if (event.target === elements.adminModal) {
                closeAdminEditModal();
            }
        }
    );

    elements.adminForm?.addEventListener(
        "submit",
        (event) => {
            event.preventDefault();

            const formData = new FormData();
            formData.append("csrf_token", window.APP_CONFIG.csrfToken);
            formData.append("original_slug", elements.adminOriginalSlug.value);
            formData.append("name", elements.adminName.value.trim());
            formData.append("slug", elements.adminSlug.value.trim());
            formData.append("municipality", elements.adminMunicipality.value.trim());
            formData.append("category", elements.adminCategory.value);
            formData.append("tag", elements.adminTag.value);
            formData.append("field_note", elements.adminFieldNote.value.trim());
            formData.append("location", elements.adminLocation.value.trim());
            formData.append("image_url", elements.adminImageUrl.value.trim());
            formData.append("description", elements.adminDescription.value.trim());

            if (elements.adminImageFile.files[0]) {
                formData.append("image_file", elements.adminImageFile.files[0]);
            }

            fetch("api/admin_save_destination.php", {
                method: "POST",
                body: formData
            })
                .then((res) => res.json())
                .then((data) => {
                    if (!data.ok) {
                        throw new Error(data.error || "Save failed");
                    }

                    applyDestinationUpdate(data.destination);
                    closeAdminEditModal();
                    renderCards();

                    if (
                        state.selected &&
                        state.selected.id === data.destination.id
                    ) {
                        renderModal(state.selected);
                    }

                    showToast("Place saved.", "success");
                })
                .catch((error) => {
                    if (elements.adminError) {
                        elements.adminError.textContent =
                            error.message || "Couldn't save this place.";
                        elements.adminError.classList.remove("hidden");
                    }
                });
        }
    );

    elements.adminDeleteBtn?.addEventListener(
        "click",
        () => {
            const slug = elements.adminOriginalSlug.value;

            if (
                !slug ||
                !confirm("Delete this place? This cannot be undone.")
            ) {
                return;
            }

            fetch("api/admin_delete_destination.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    slug,
                    csrf_token: window.APP_CONFIG.csrfToken
                })
            })
                .then((res) => res.json())
                .then((data) => {
                    if (!data.ok) {
                        throw new Error(data.error || "Delete failed");
                    }

                    const index = destinations.findIndex(
                        (place) => place.id === slug
                    );

                    if (index !== -1) {
                        destinations.splice(index, 1);
                    }

                    closeAdminEditModal();
                    closeModal();
                    renderCards();
                    showToast("Place deleted.", "success");
                })
                .catch((error) => {
                    if (elements.adminError) {
                        elements.adminError.textContent =
                            error.message || "Couldn't delete this place.";
                        elements.adminError.classList.remove("hidden");
                    }
                });
        }
    );

    elements.writeReviewBtn?.addEventListener(
        "click",
        () => {
            if (!window.APP_CONFIG || !window.APP_CONFIG.loggedIn) {
                window.location.href = "auth.php";
                return;
            }

            elements.reviewForm
                ?.classList.toggle(
                    "hidden"
                );

            const isOpen =
                elements.reviewForm &&
                !elements.reviewForm.classList.contains(
                    "hidden"
                );

            if (isOpen) {
                state.reviewRating = state.myReview
                    ? state.myReview.rating
                    : 5;

                if (elements.reviewText) {
                    elements.reviewText.value = state.myReview
                        ? state.myReview.text
                        : "";
                }

                renderStarInput();
                elements.reviewText?.focus();
            }
        }
    );

    elements.cancelReviewBtn?.addEventListener(
        "click",
        resetReviewForm
    );

    elements.submitReviewBtn?.addEventListener(
        "click",
        submitReview
    );

    elements.starInput?.addEventListener(
        "click",
        (event) => {
            const star =
                event.target.closest(
                    "[data-star]"
                );

            if (!star) {
                return;
            }

            state.reviewRating =
                Number(star.dataset.star);

            renderStarInput();
        }
    );

    document
        .querySelector("#hero-image")
        ?.addEventListener(
            "error",
            (event) => {
                event.target.parentElement
                    .classList.add(
                        "has-failed"
                    );
            }
        );

    /* ---------------------------------------------------------------
       Initial destination page setup
       --------------------------------------------------------------- */

    populateFilters();

    elements.municipality.value =
        state.municipality;

    elements.category.value =
        state.category;

    renderCards();

    /*
     * Deep link support:
     *
     * destination.php?place=<id>
     */
    const requestedId =
        new URLSearchParams(
            window.location.search
        ).get("place");

    if (requestedId) {
        const requestedPlace =
            destinations.find(
                (place) =>
                    place.id ===
                    requestedId
            );

        if (requestedPlace) {
            document
                .querySelector("#places")
                ?.scrollIntoView({
                    behavior:
                        "instant" in window
                            ? "instant"
                            : "auto"
                });

            renderModal(
                requestedPlace
            );
        }
    }
}


/* ===================================================================
   SHARED FAVORITES
   =================================================================== */

function readFavorites() {
    try {
        return JSON.parse(
            localStorage.getItem(
                "travelbuddies-favorites"
            ) || "[]"
        );
    } catch {
        return [];
    }
}

/**
 * The favorites cache is only ever meaningful for whoever is logged in.
 * Without this, logging out leaves the previous account's saved count
 * showing to the next (guest or different) visitor on this browser.
 */
function clearLocalFavorites() {
    localStorage.removeItem("travelbuddies-favorites");
}

/**
 * Shows a dismissible toast pinned to the top of the viewport. Auto-
 * removes after `duration` ms, or immediately if the person clicks the
 * close button — whichever comes first.
 */
function showToast(message, type = "success", duration = 5000) {
    let container = document.querySelector(".toast-container");

    if (!container) {
        container = document.createElement("div");
        container.className = "toast-container";
        document.body.appendChild(container);
    }

    const toast = document.createElement("div");
    toast.className = `toast toast-${type}`;

    const text = document.createElement("span");
    text.className = "toast-message";
    text.textContent = message;

    const closeBtn = document.createElement("button");
    closeBtn.className = "toast-close";
    closeBtn.type = "button";
    closeBtn.setAttribute("aria-label", "Dismiss notification");
    closeBtn.textContent = "×";

    toast.appendChild(text);
    toast.appendChild(closeBtn);
    container.appendChild(toast);

    const remove = () => {
        toast.classList.add("is-leaving");
        toast.addEventListener(
            "animationend",
            () => toast.remove(),
            { once: true }
        );
    };

    const timer = setTimeout(remove, duration);
    closeBtn.addEventListener("click", () => {
        clearTimeout(timer);
        remove();
    });
}


/**
 * For a logged-in visitor, localStorage is just a fast local cache —
 * the database (via api/get_favorites.php) is the source of truth.
 * Call this once before the first render so readFavorites() already
 * reflects what's saved on the account, not just this browser.
 */
async function syncFavoritesFromServer() {
    if (!window.APP_CONFIG || !window.APP_CONFIG.loggedIn) {
        return;
    }

    try {
        const res = await fetch("api/get_favorites.php");
        const data = await res.json();

        if (data.ok) {
            localStorage.setItem(
                "travelbuddies-favorites",
                JSON.stringify(data.favorites)
            );
        }
    } catch {
        // Offline or the API isn't reachable — fall back to
        // whatever was already cached in this browser.
    }
}


function wireImageFallbacks() {
    document
        .querySelectorAll(
            ".image-frame img"
        )
        .forEach((img) => {
            img.addEventListener(
                "error",
                () => {
                    img.parentElement.classList.add(
                        "has-failed"
                    );
                },
                {
                    once: true
                }
            );
        });
}


function updateCounts() {
    const count =
        readFavorites().length;

    const navCount =
        document.querySelector(
            "#nav-saved-count"
        );

    const mobileCount =
        document.querySelector(
            "#mobile-saved-count"
        );

    if (navCount) {
        navCount.textContent =
            count;
    }

    if (mobileCount) {
        mobileCount.textContent =
            count;
    }
}


/* ===================================================================
   PROFILE PAGE
   =================================================================== */

function switchProfileTab(tab) {
    const tabButton =
        document.getElementById(
            "tab-" + tab
        );

    const panel =
        document.getElementById(
            "panel-" + tab
        );

    if (!tabButton || !panel) {
        return;
    }

    document
        .querySelectorAll(
            ".profile-tab-btn"
        )
        .forEach((btn) =>
            btn.classList.remove(
                "active"
            )
        );

    document
        .querySelectorAll(
            ".profile-panel"
        )
        .forEach((p) =>
            p.classList.remove(
                "active"
            )
        );

    tabButton.classList.add(
        "active"
    );

    panel.classList.add(
        "active"
    );
}


function toggleEditMode() {
    const tabsSection =
        document.getElementById(
            "tabsSection"
        );

    const editSection =
        document.getElementById(
            "editBioSection"
        );

    const editBtn =
        document.getElementById(
            "editToggleBtn"
        );

    if (
        !tabsSection ||
        !editSection ||
        !editBtn
    ) {
        return;
    }

    const isEditing =
        editSection.style.display !==
        "none";

    if (isEditing) {
        tabsSection.style.display =
            "block";

        editSection.style.display =
            "none";

        editBtn.textContent =
            "Edit Profile";
    } else {
        tabsSection.style.display =
            "none";

        editSection.style.display =
            "block";

        editBtn.textContent =
            "Cancel";
    }
}


function saveBio() {
    const textarea =
        document.getElementById(
            "bioTextarea"
        );

    const display =
        document.getElementById(
            "bioDisplay"
        );

    if (!textarea || !display) {
        return;
    }

    display.textContent =
        textarea.value.trim();

    toggleEditMode();
}


function saveSettings() {
    const name =
        document.getElementById(
            "settingsName"
        )?.value ?? "";

    const email =
        document.getElementById(
            "settingsEmail"
        )?.value ?? "";

    const location =
        document.getElementById(
            "settingsLocation"
        )?.value ?? "";

    const newPassword =
        document.getElementById(
            "settingsNewPassword"
        )?.value ?? "";

    const confirmPassword =
        document.getElementById(
            "settingsConfirmPassword"
        )?.value ?? "";

    if (
        newPassword ||
        confirmPassword
    ) {
        if (
            newPassword !==
            confirmPassword
        ) {
            alert(
                "Passwords don't match. Please try again."
            );

            return;
        }
    }

    const headerName =
        document.getElementById(
            "headerName"
        );

    const headerEmail =
        document.getElementById(
            "headerEmail"
        );

    const headerLocation =
        document.getElementById(
            "headerLocation"
        );

    if (headerName) {
        headerName.textContent =
            name;
    }

    if (headerEmail) {
        headerEmail.textContent =
            email;
    }

    if (headerLocation) {
        headerLocation.textContent =
            location;
    }

    const newPasswordField =
        document.getElementById(
            "settingsNewPassword"
        );

    const confirmPasswordField =
        document.getElementById(
            "settingsConfirmPassword"
        );

    if (newPasswordField) {
        newPasswordField.value =
            "";
    }

    if (confirmPasswordField) {
        confirmPasswordField.value =
            "";
    }

    const confirmMessage =
        document.getElementById(
            "saveConfirm"
        );

    if (confirmMessage) {
        confirmMessage.classList.add(
            "show"
        );

        window.setTimeout(
            () =>
                confirmMessage.classList.remove(
                    "show"
                ),
            2500
        );
    }
}


/*
 * FIXED LOGOUT
 *
 * LogIn.php was removed.
 * logout.php is now responsible for destroying the session.
 */
function logOut() {
    window.location.href =
        "logout.php";
}


function previewPhoto(event) {
    const file =
        event.target.files[0];

    if (!file) {
        return;
    }

    const reader =
        new FileReader();

    reader.onload =
        (loadEvent) => {
            const preview =
                document.getElementById(
                    "editAvatarPreview"
                );

            if (!preview) {
                return;
            }

            preview.style.backgroundImage =
                `url(${loadEvent.target.result})`;

            preview.style.backgroundSize =
                "cover";

            preview.style.backgroundPosition =
                "center";

            preview.textContent =
                "";
        };

    reader.readAsDataURL(file);
}


/* ===================================================================
   BOOT
   =================================================================== */

document.addEventListener(
    "DOMContentLoaded",
    async () => {
        // Wait for the account's real favorites and the real ratings
        // before the first render, so nobody sees a flash of the wrong
        // state or a fabricated number.
        if (window.APP_CONFIG && window.APP_CONFIG.loggedIn) {
            await Promise.all([
                syncFavoritesFromServer(),
                syncRatingsFromServer(),
                syncDestinationsFromServer()
            ]);
        } else {
            // Guests can't save places, so any favorites still sitting
            // in this browser are leftovers from a previous account
            // (most commonly: right after logging out).
            clearLocalFavorites();
            await Promise.all([
                syncRatingsFromServer(),
                syncDestinationsFromServer()
            ]);
        }

        if (window.APP_CONFIG && window.APP_CONFIG.isAdmin) {
            document
                .querySelectorAll(".admin-only")
                .forEach((el) => el.classList.remove("hidden"));
        }

        initHomePage();
        initDestinationPage();
        updateCounts();
    }
);
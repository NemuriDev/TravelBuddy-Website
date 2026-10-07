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

const destinations = [];

/* -------------------------------------------------------------------
   Destination rating helpers
   Real average rating + review count, loaded from the database via
   api/get_ratings.php and cached here for both the home page and the
   full guide to read from. A place with no reviews yet is "New", not
   a made-up number.
   ------------------------------------------------------------------- */

let ratingsCache = {};

/**
 * Inserts a new place, or replaces an existing one keyed by slug.
 * `previousId` is the slug the place had before an admin edit, so a
 * changed slug replaces its old entry instead of adding a duplicate.
 */
function applyDestinationUpdate(place, previousId = place.id) {
    const index = destinations.findIndex(
        (existing) => existing.id === previousId
    );

    if (index === -1) {
        destinations.push(place);
    } else {
        destinations[index] = place;
    }
}

/**
 * The `destinations` DB table is the only source of places. Whatever
 * the admin panel adds, edits, or deletes is what the guide shows.
 */
async function syncDestinationsFromServer() {
    try {
        const res = await fetch("api/get_destinations.php");
        const data = await res.json();

        if (!data.ok) {
            throw new Error(data.error || "Load failed");
        }

        destinations.splice(0, destinations.length, ...data.destinations);
    } catch {
        showToast(
            "Couldn't load the places right now. Please refresh the page.",
            "error"
        );
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

/**
 * A card's photos are the main image followed by the gallery, so the
 * slider has no data of its own: whatever the admin form saves as
 * image_url / gallery_images is what slides. Pass `href` when the
 * photo should link somewhere (the home page's featured cards).
 */
function cardSliderMarkup(place, { href = null } = {}) {
    const urls = [place.imageUrl, ...place.gallery].filter(Boolean);

    if (urls.length === 0) {
        urls.push("");
    }

    const name = escapeHtml(place.name);
    const tag = href ? "a" : "div";
    const link = href ? ` href="${href}" aria-label="View ${name}"` : "";

    const slides = urls.map((url, index) => `
        <${tag} class="card-slide image-frame"${link}>
            <img
                src="${escapeHtml(url)}"
                alt="${name} in ${escapeHtml(place.municipality)}${urls.length > 1 ? `, photo ${index + 1}` : ""}"
                loading="lazy"
            />
            <span class="image-fallback" aria-hidden="true">◉</span>
        </${tag}>`).join("");

    if (urls.length === 1) {
        return `<div class="card-slider"><div class="card-slides">${slides}</div></div>`;
    }

    const dots = urls
        .map((_, index) => `<span class="slider-dot${index === 0 ? " is-active" : ""}"></span>`)
        .join("");

    return `
        <div class="card-slider">
            <div class="card-slides">${slides}</div>
            <button class="slider-arrow slider-prev" type="button" data-slide-dir="-1" aria-label="Previous photo of ${name}">‹</button>
            <button class="slider-arrow slider-next" type="button" data-slide-dir="1" aria-label="Next photo of ${name}">›</button>
            <div class="slider-dots" aria-hidden="true">${dots}</div>
        </div>`;
}

/**
 * Delegated on document so cards re-rendered by filters, favorites, or
 * an admin save never need their sliders re-wired.
 */
function initCardSliders() {
    const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)");

    document.addEventListener("click", (event) => {
        const arrow = event.target.closest("[data-slide-dir]");

        if (!arrow) {
            return;
        }

        const track = arrow.parentElement.querySelector(".card-slides");
        const count = track.children.length;
        const current = Math.round(track.scrollLeft / track.clientWidth);
        const target = (current + Number(arrow.dataset.slideDir) + count) % count;

        track.scrollTo({
            left: target * track.clientWidth,
            behavior: reduceMotion.matches ? "auto" : "smooth"
        });
    });

    // scroll events don't bubble, so listen in the capture phase.
    document.addEventListener("scroll", (event) => {
        const track = event.target;

        if (!(track instanceof Element) || !track.classList.contains("card-slides")) {
            return;
        }

        const current = Math.round(track.scrollLeft / track.clientWidth);

        track.parentElement
            .querySelectorAll(".slider-dot")
            .forEach((dot, index) => {
                dot.classList.toggle("is-active", index === current);
            });
    }, true);
}

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

    grid.innerHTML = featuredIds.map((id, index) => {
        const place = findDestination(id);

        if (!place) {
            return "";
        }

        const { rating, reviews } = ratingFor(place);
        

        return `
        <article class="destination-card" style="animation-delay:${index * .06}s">

            <div class="card-media image-frame card-media--link">
                ${cardSliderMarkup(place, { href: `destination.php?place=${encodeURIComponent(place.id)}#places` })}

                <span class="card-badge category-label">
                    ${escapeHtml(place.category)}
                </span>

                <span class="card-badge rating-badge">
                    <span class="star" aria-hidden="true">★</span>
                    ${rating}
                </span>
            </div>

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
        showSaved: new URLSearchParams(window.location.search).get("saved") === "1",
        favorites: readFavorites(),
        visited: readVisited(),
        reviewRating: 5,
        selected: null,
        myReview: null
    };

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
        modalGallery: document.querySelector("#modal-gallery"),
        modalKicker: document.querySelector("#modal-kicker"),
        modalTitle: document.querySelector("#modal-title"),
        modalMunicipality: document.querySelector("#modal-municipality"),
        modalRating: document.querySelector("#modal-rating"),
        modalDescription: document.querySelector("#modal-description"),
        modalTags: document.querySelector("#modal-tags"),
        modalLocation: document.querySelector("#modal-location"),
        modalMapLink: document.querySelector("#modal-map-link"),
        modalTime: document.querySelector("#modal-time"),
        modalEco: document.querySelector("#modal-eco"),
        modalEcoList: document.querySelector("#modal-eco-list"),
        modalFavorite: document.querySelector("#modal-favorite"),
        modalVisited: document.querySelector("#modal-visited"),
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
        adminMapsUrl: document.querySelector("#admin-field-maps-url"),
        adminImageUrl: document.querySelector("#admin-field-image-url"),
        adminImageFile: document.querySelector("#admin-field-image-file"),
        adminGalleryUrls: [...document.querySelectorAll(".admin-gallery-url")],
        adminGalleryFiles: [...document.querySelectorAll(".admin-gallery-file")],
        adminDescription: document.querySelector("#admin-field-description"),
        adminEco: document.querySelector("#admin-field-eco"),
        adminError: document.querySelector("#admin-form-error"),
        adminDeleteBtn: document.querySelector("#admin-delete-btn")
    };

    function writeFavorites() {
        localStorage.setItem(
            "travelbuddies-favorites",
            JSON.stringify(state.favorites)
        );
    }

    function writeVisited() {
        localStorage.setItem(
            "travelbuddies-visited",
            JSON.stringify(state.visited)
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

                        ${cardSliderMarkup(place)}

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
            renderModal(state.selected, { keepReviewDraft: true });
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
            .catch((error) => {
                showToast(apiFailureMessage(error, "Couldn't save that right now. Please try again."), "error");
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
                    renderModal(state.selected, { keepReviewDraft: true });
                }
            });
    }

    function toggleVisited(id) {
        if (!window.APP_CONFIG || !window.APP_CONFIG.loggedIn) {
            window.location.href = "auth.php";
            return;
        }

        const wasVisited = state.visited.includes(id);

        state.visited = wasVisited
            ? state.visited.filter(
                (item) => item !== id
            )
            : [...state.visited, id];

        writeVisited();

        if (
            state.selected &&
            state.selected.id === id
        ) {
            renderModal(state.selected, { keepReviewDraft: true });
        }

        fetch("api/toggle_visited.php", {
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
            .catch((error) => {
                showToast(apiFailureMessage(error, "Couldn't save that right now. Please try again."), "error");
                // Couldn't save to the account — undo the optimistic
                // update so the UI doesn't claim it's visited when it isn't.
                state.visited = wasVisited
                    ? [...state.visited, id]
                    : state.visited.filter((item) => item !== id);

                writeVisited();

                if (
                    state.selected &&
                    state.selected.id === id
                ) {
                    renderModal(state.selected, { keepReviewDraft: true });
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

        // Loading, not fabricated content, while the real reviews load.
        elements.reviewList.innerHTML = `
            <p class="review-empty">Loading reviews…</p>
        `;

        fetch(
            `api/get_reviews.php?slug=${encodeURIComponent(place.id)}`
        )
            .then((res) => res.json())
            .then((data) => {
                if (!data.ok || !state.selected || state.selected.id !== place.id) {
                    return;
                }

                // A real empty result is honest — show it rather than
                // leaving old content up.
                paintReviews(data.reviews);

                state.myReview = data.mine || null;
                updateReviewButtonLabels();
            })
            .catch(() => {
                elements.reviewList.innerHTML = `
                    <p class="review-empty">Couldn't load reviews right now. Try again shortly.</p>
                `;
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
                                ${
                                    review.photo
                                        ? `<img src="${escapeHtml(review.photo)}" alt="" class="review-avatar-image">`
                                        : escapeHtml(review.initials)
                                }
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
            .catch((error) => {
                showToast(
                    apiFailureMessage(error, "Couldn't save your review right now. Please try again."),
                    "error"
                );
            });
    }

    function renderModal(place, { keepReviewDraft = false } = {}) {
        state.selected = place;

        const { rating, reviews } =
            ratingFor(place);

        elements.modalImage.src =
            place.imageUrl || "";

        elements.modalImage.alt =
            `${place.name} in ${place.municipality}`;

        elements.modalGallery.innerHTML = place.gallery
            .map((url, index) => `
                <div class="detail-thumb image-frame">
                    <img
                        src="${escapeHtml(url)}"
                        alt="${escapeHtml(place.name)} photo ${index + 2}"
                        loading="lazy"
                    />
                    <span class="image-fallback" aria-hidden="true">◉</span>
                </div>
            `)
            .join("");

        elements.modalGallery.style.setProperty(
            "--thumbs",
            place.gallery.length
        );

        wireImageFallbacks();

        elements.modalKicker.textContent =
            `${place.category} / ${place.tag || ""}`;

        elements.modalTitle.textContent =
            place.name;

        elements.modalMunicipality.textContent =
            place.municipality;

        elements.modalRating.innerHTML = `
            <span aria-hidden="true">★</span>
            ${rating}
            <span class="count">
                (${reviews} reviews)
            </span>
        `;

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

        if (elements.modalMapLink) {
            const mapsHref = place.mapsUrl ||
                (place.location
                    ? `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(place.location)}`
                    : null);

            if (mapsHref) {
                elements.modalMapLink.href = mapsHref;
                elements.modalMapLink.classList.remove("hidden");
            } else {
                elements.modalMapLink.removeAttribute("href");
                elements.modalMapLink.classList.add("hidden");
            }
        }

        elements.modalTime.textContent =
            place.time || "";

        const ecoLines = place.ecoGuidelines
            .split("\n")
            .map((line) => line.trim())
            .filter(Boolean);

        elements.modalEcoList.innerHTML = ecoLines
            .map((line) => `<li>${escapeHtml(line)}</li>`)
            .join("");

        elements.modalEco.classList.toggle("hidden", ecoLines.length === 0);

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

        const isVisited =
            state.visited.includes(place.id);

        elements.modalVisited.innerHTML = `
            ${isVisited ? "✓" : "⚑"}
            ${
                isVisited
                    ? "Visited"
                    : "Mark as Visited"
            }
        `;

        elements.modalVisited.classList.toggle(
            "is-visited",
            isVisited
        );

        elements.shareLabel.textContent =
            "Share the note";

        if (!keepReviewDraft) {
            resetReviewForm();
            renderReviews(place);
        }
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

    elements.modalVisited?.addEventListener(
        "click",
        () => {
            if (state.selected) {
                toggleVisited(
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
        elements.adminForm.reset();

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
            elements.adminMapsUrl.value = place.mapsUrl || "";
            elements.adminImageUrl.value = place.imageUrl || "";
            elements.adminGalleryUrls.forEach((input, index) => {
                input.value = place.gallery[index] || "";
            });
            elements.adminDescription.value = place.description || "";
            elements.adminEco.value = place.ecoGuidelines;
            elements.adminDeleteBtn?.classList.remove("hidden");
        } else {
            elements.adminTitle.textContent = "Add a new place";
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

            const previousId = elements.adminOriginalSlug.value;

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
            formData.append("maps_url", elements.adminMapsUrl.value.trim());
            formData.append("image_url", elements.adminImageUrl.value.trim());
            formData.append("description", elements.adminDescription.value.trim());
            formData.append("eco_guidelines", elements.adminEco.value.trim());

            if (elements.adminImageFile.files[0]) {
                formData.append("image_file", elements.adminImageFile.files[0]);
            }

            elements.adminGalleryUrls.forEach((input, index) => {
                const slot = index + 1;
                const file = elements.adminGalleryFiles[index].files[0];

                formData.append(`gallery_url_${slot}`, input.value.trim());

                if (file) {
                    formData.append(`gallery_file_${slot}`, file);
                }
            });

            fetch("api/admin_save_destination.php", {
                method: "POST",
                body: formData
            })
                .then((res) => res.json())
                .then((data) => {
                    if (!data.ok) {
                        throw new Error(data.error || "Save failed");
                    }

                    const replacedId = previousId || data.destination.id;
                    applyDestinationUpdate(data.destination, replacedId);
                    closeAdminEditModal();
                    renderCards();

                    if (
                        state.selected &&
                        state.selected.id === replacedId
                    ) {
                        renderModal(data.destination, { keepReviewDraft: true });
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

function readVisited() {
    try {
        return JSON.parse(
            localStorage.getItem(
                "travelbuddies-visited"
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

function clearLocalVisited() {
    localStorage.removeItem("travelbuddies-visited");
}

/**
 * Server-side rejections are thrown as plain Errors carrying the
 * server's message; anything else (offline, bad JSON) gets `fallback`.
 */
function apiFailureMessage(error, fallback) {
    return error.name === "Error" ? error.message : fallback;
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

async function syncVisitedFromServer() {
    if (!window.APP_CONFIG || !window.APP_CONFIG.loggedIn) {
        return;
    }

    try {
        const res = await fetch("api/get_visited.php");
        const data = await res.json();

        if (data.ok) {
            localStorage.setItem(
                "travelbuddies-visited",
                JSON.stringify(data.visited)
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


/**
 * Outside the guide page there is no list to filter, so the Saved
 * buttons send the visitor to the guide with the saved filter on.
 */
function initSavedNav() {
    if (document.querySelector("#destination-grid")) {
        return;
    }

    document
        .querySelectorAll("#nav-saved, #mobile-saved")
        .forEach((button) => {
            button.addEventListener("click", () => {
                window.location.href = "destination.php?saved=1#places";
            });
        });
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
                syncVisitedFromServer(),
                syncRatingsFromServer(),
                syncDestinationsFromServer()
            ]);
        } else {
            // Guests can't save places, so any favorites still sitting
            // in this browser are leftovers from a previous account
            // (most commonly: right after logging out).
            clearLocalFavorites();
            clearLocalVisited();
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

        initCardSliders();
        initHomePage();
        initDestinationPage();
        initSavedNav();
        updateCounts();
    }
);
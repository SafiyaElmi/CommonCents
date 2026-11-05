// Listing page interactions: image thumbnails, favourites and cart feedback.
function initializeListingPrototype() {
    document.addEventListener('DOMContentLoaded', () => {
        const mainImage = document.getElementById('mainImage');
        const thumbnails = document.querySelectorAll('.thumbnail');
        if (!mainImage || !thumbnails.length)
            return;
        thumbnails.forEach(thumb => {
            thumb.addEventListener('click', () => {
                thumbnails.forEach(t => t.classList.remove('active'));
                thumb.classList.add('active');
                const newSrc = thumb.getAttribute('data-img');
                if (newSrc)
                    mainImage.setAttribute('src', newSrc);
            });
        });
        const favoriteBtn = document.querySelector('.favorite');
        if (favoriteBtn) {
            favoriteBtn.addEventListener('click', () => {
                favoriteBtn.classList.toggle('active');
                favoriteBtn.textContent = favoriteBtn.classList.contains('active') ? '♥' : '♡';
            });
        }
        const addToCartBtn = document.querySelector('.add-to-cart');
        if (addToCartBtn) {
            addToCartBtn.addEventListener('click', () => {
                addToCartBtn.textContent = 'Added ✓';
                setTimeout(() => {
                    addToCartBtn.textContent = '🛒 Add to Cart';
                }, 1500);
            });
        }
    });
}
initializeListingPrototype();
// Cart page interactions: stores demo cart items and updates quantities and totals.
function initializeCartPrototype() {
    if (!document.getElementById("cartItems"))
        return;
    const CART_STORAGE_KEY = "studentMarketCart";
    const DELIVERY_FEE = 50;
    const demoCart = [
        { cartItemID: 1, listingID: 101, name: "iPhone 13", category: "Phones", seller: "TechZone", price: 10499, quantity: 1, image: "iphone.jpg" },
        { cartItemID: 2, listingID: 102, name: "MacBook Air (M1)", category: "Laptops", seller: "StudentTech", price: 19999, quantity: 1, image: "laptop.jpg" },
        { cartItemID: 3, listingID: 103, name: "Wireless Headphones", category: "Electronics", seller: "AudioHub", price: 599, quantity: 2, image: "headphones.jpg" }
    ];
    let cart = loadCart();
    function loadCart() {
        try {
            const saved = localStorage.getItem(CART_STORAGE_KEY);
            if (!saved)
                return demoCart.map(item => ({ ...item }));
            const parsed = JSON.parse(saved);
            return Array.isArray(parsed) ? parsed : demoCart.map(item => ({ ...item }));
        }
        catch (error) {
            return demoCart.map(item => ({ ...item }));
        }
    }
    function saveCart() {
        localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(cart));
    }
    function formatCurrency(value) {
        return new Intl.NumberFormat("en-ZA", {
            style: "currency",
            currency: "ZAR",
            minimumFractionDigits: 2
        }).format(value).replace("ZAR", "R").trim();
    }
    function getTotalQuantity() {
        return cart.reduce((total, item) => total + Number(item.quantity), 0);
    }
    function getSubtotal() {
        return cart.reduce((total, item) => total + (Number(item.price) * Number(item.quantity)), 0);
    }
    function renderCart(filter = "") {
        const container = document.getElementById("cartItems");
        const emptyState = document.getElementById("emptyCart");
        const clearButton = document.getElementById("clearCart");
        const query = filter.trim().toLowerCase();
        const visibleItems = cart.filter(item => item.name.toLowerCase().includes(query) ||
            item.category.toLowerCase().includes(query) ||
            item.seller.toLowerCase().includes(query));
        container.innerHTML = "";
        if (cart.length === 0) {
            emptyState.hidden = false;
            clearButton.disabled = true;
        }
        else {
            emptyState.hidden = true;
            clearButton.disabled = false;
        }
        if (cart.length > 0 && visibleItems.length === 0) {
            container.innerHTML = `<p class="no-results">No cart items match your search.</p>`;
        }
        visibleItems.forEach(item => {
            const itemTotal = Number(item.price) * Number(item.quantity);
            const card = document.createElement("article");
            card.className = "cart-item";
            card.dataset.id = item.cartItemID;
            card.innerHTML = `
              <div class="item-image">
                  <img src="${item.image}" alt="${item.name}">
              </div>
              <div class="item-info">
                  <h3>${item.name}</h3>
                  <p>Category: ${item.category}</p>
                  <p class="seller">Seller: ${item.seller}</p>
                  <p class="unit-price">${formatCurrency(item.price)} each</p>
              </div>
              <div class="item-actions">
                  <strong class="item-total">${formatCurrency(itemTotal)}</strong>
                  <div class="quantity-control" aria-label="Quantity controls">
                      <button type="button" data-action="decrease" aria-label="Decrease quantity">−</button>
                      <span>${item.quantity}</span>
                      <button type="button" data-action="increase" aria-label="Increase quantity">+</button>
                  </div>
                  <button type="button" class="remove-button" data-action="remove">Remove</button>
              </div>
          `;
            container.appendChild(card);
        });
        updateSummary();
    }
    function updateSummary() {
        const quantity = getTotalQuantity();
        const subtotal = getSubtotal();
        const delivery = cart.length ? DELIVERY_FEE : 0;
        const total = subtotal + delivery;
        document.getElementById("itemCountLabel").textContent = `(${quantity})`;
        document.getElementById("summaryItems").textContent = quantity;
        document.getElementById("subtotal").textContent = formatCurrency(subtotal);
        document.getElementById("deliveryFee").textContent = formatCurrency(delivery);
        document.getElementById("grandTotal").textContent = formatCurrency(total);
        document.getElementById("navCartCount").textContent = quantity;
        document.getElementById("checkoutButton").disabled = cart.length === 0;
    }
    function changeQuantity(id, change) {
        const item = cart.find(cartItem => String(cartItem.cartItemID) === String(id));
        if (!item)
            return;
        item.quantity = Math.max(1, Number(item.quantity) + change);
        saveCart();
        renderCart(document.getElementById("cartSearch").value);
        showNotice("Cart updated.");
    }
    function removeItem(id) {
        const item = cart.find(cartItem => String(cartItem.cartItemID) === String(id));
        cart = cart.filter(cartItem => String(cartItem.cartItemID) !== String(id));
        saveCart();
        renderCart(document.getElementById("cartSearch").value);
        showNotice(item ? `${item.name} was removed from your cart.` : "Item removed.");
    }
    function showNotice(message) {
        const notice = document.getElementById("cartNotice");
        notice.textContent = message;
        clearTimeout(showNotice.timeout);
        showNotice.timeout = setTimeout(() => { notice.textContent = ""; }, 2200);
    }
    document.getElementById("cartItems").addEventListener("click", event => {
        const button = event.target.closest("button[data-action]");
        if (!button)
            return;
        const card = button.closest(".cart-item");
        const id = card.dataset.id;
        const action = button.dataset.action;
        if (action === "increase")
            changeQuantity(id, 1);
        if (action === "decrease")
            changeQuantity(id, -1);
        if (action === "remove")
            removeItem(id);
    });
    document.getElementById("clearCart").addEventListener("click", () => {
        if (!cart.length)
            return;
        if (confirm("Clear all items from your cart?")) {
            cart = [];
            saveCart();
            renderCart();
            showNotice("Your cart has been cleared.");
        }
    });
    document.getElementById("cartSearch").addEventListener("input", event => {
        renderCart(event.target.value);
    });
    document.getElementById("checkoutButton").addEventListener("click", () => {
        if (!cart.length)
            return;
        showNotice("Checkout is ready to connect to the Order/Payment backend.");
    });
    renderCart();
}
initializeCartPrototype();
// Order page search: filters order cards by product name or order number.
function initializeOrdersPrototype() {
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.querySelector('.search-bar input');
        if (!searchInput)
            return;
        // Supports both order cards and individual item cards.
        const cards = document.querySelectorAll('.order-card, .item-card');
        if (!cards.length)
            return;
        let noResultsMsg = document.querySelector('.no-results-msg');
        if (!noResultsMsg) {
            noResultsMsg = document.createElement('p');
            noResultsMsg.className = 'no-results-msg';
            noResultsMsg.textContent = 'No matching items found.';
            noResultsMsg.style.display = 'none';
            noResultsMsg.style.textAlign = 'center';
            noResultsMsg.style.padding = '2rem 0';
            cards[0].parentElement.appendChild(noResultsMsg);
        }
        function getCardName(card) {
            const nameEl = card.querySelector('.product-details h3, .item-info h3');
            return nameEl ? nameEl.textContent.trim().toLowerCase() : '';
        }
        function getOrderId(card) {
            // Read the order number when the card includes one.
            const idEl = card.querySelector('.order-top h3');
            return idEl ? idEl.textContent.trim().toLowerCase() : '';
        }
        function filterCards() {
            const query = searchInput.value.trim().toLowerCase();
            let visibleCount = 0;
            cards.forEach(card => {
                const name = getCardName(card);
                const orderId = getOrderId(card);
                const matches = query === '' || name.includes(query) || orderId.includes(query);
                card.style.display = matches ? '' : 'none';
                if (matches)
                    visibleCount++;
            });
            noResultsMsg.style.display = visibleCount === 0 ? 'block' : 'none';
        }
        searchInput.addEventListener('input', filterCards);
    });
}
initializeOrdersPrototype();
// Shared interactions used by the PHP pages.
window.goBack = function () {
    window.history.back();
};
window.updateNotificationCount = function () {
    const cards = document.querySelectorAll('.notification-card');
    const count = document.getElementById('notificationCount');
    if (count)
        count.textContent = cards.length;
};
window.markAsRead = function (id) {
    const card = document.getElementById('notification-' + id);
    if (!card)
        return;
    card.classList.remove('unread');
    const dot = card.querySelector('.unread-dot');
    if (dot)
        dot.remove();
    const button = card.querySelector('.notification-actions > button');
    if (button)
        button.remove();
};
window.markAllAsRead = function () {
    document.querySelectorAll('.notification-card').forEach((card) => {
        card.classList.remove('unread');
        const dot = card.querySelector('.unread-dot');
        if (dot)
            dot.remove();
        const button = card.querySelector('.notification-actions > button');
        if (button)
            button.remove();
    });
};
window.checkEmptyNotifications = function () {
    const cards = document.querySelectorAll('.notification-card');
    const empty = document.getElementById('emptyNotifications');
    if (empty)
        empty.classList.toggle('hidden', cards.length !== 0);
};
window.selectRating = function (rating) {
    const input = document.getElementById('rating');
    if (!input)
        return;
    input.value = rating;
    document.querySelectorAll('.star-selector button').forEach((button, index) => {
        button.classList.toggle('selected', index < rating);
    });
};
window.loadReview = function (rating, comment) {
    const commentField = document.getElementById('reviewComment');
    if (!commentField)
        return;
    commentField.value = comment;
    window.selectRating(rating);
    commentField.focus();
    window.scrollTo({ top: 0, behavior: 'smooth' });
};
document.addEventListener('DOMContentLoaded', () => {
    window.updateNotificationCount();
    window.checkEmptyNotifications();
    document.querySelectorAll('.auto-dismiss').forEach((message) => {
        window.setTimeout(() => message.remove(), 5000);
    });
});

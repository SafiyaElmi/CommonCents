// Original Frontend.zip module: Category Frontend/category.js
function initializeCategoryPrototype() {
    if (!document.getElementById("categoryChips"))
        return;
    const API_URL = "http://localhost:8080/api/category";
    let categories = [
        { categoryID: "CAT-BOOKS", categoryName: "Books" },
        { categoryID: "CAT-ELEC", categoryName: "Electronics" },
        { categoryID: "CAT-FASH", categoryName: "Fashion" },
        { categoryID: "CAT-HOME", categoryName: "Home" }
    ];
    let isEditMode = false;
    // DOM Elements
    const chipsContainer = document.getElementById("categoryChips");
    const listContainer = document.getElementById("categoryListContainer");
    const itemCountLabel = document.getElementById("itemCountLabel");
    const searchInput = document.getElementById("categorySearchInput");
    const modalBackdrop = document.getElementById("modalBackdrop");
    const categoryForm = document.getElementById("categoryForm");
    const categoryIDInput = document.getElementById("categoryIDInput");
    const categoryNameInput = document.getElementById("categoryNameInput");
    const modalTitle = document.getElementById("modalTitle");
    // Fetch All Categories (GET /api/category)
    async function fetchCategories() {
        try {
            const response = await fetch(API_URL);
            if (response.ok) {
                categories = await response.json();
            }
        }
        catch {
            console.warn("Backend unavailable; using local memory data.");
        }
        render();
    }
    // Create Category (POST /api/category)
    async function createCategory(payload) {
        try {
            const response = await fetch(API_URL, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            });
            if (response.ok) {
                const saved = await response.json();
                categories.push(saved);
                return;
            }
        }
        catch {
            // Fallback locally
            categories.push(payload);
        }
    }
    // Update Category (PUT /api/category)
    async function updateCategory(payload) {
        try {
            const response = await fetch(API_URL, {
                method: "PUT",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            });
            if (response.ok) {
                const updated = await response.json();
                const index = categories.findIndex(c => c.categoryID === updated.categoryID);
                if (index !== -1)
                    categories[index] = updated;
                return;
            }
        }
        catch {
            // Fallback locally
            const index = categories.findIndex(c => c.categoryID === payload.categoryID);
            if (index !== -1)
                categories[index] = payload;
        }
    }
    // Delete Category (DELETE /api/category/{categoryId})
    async function deleteCategory(categoryId) {
        try {
            await fetch(`${API_URL}/${categoryId}`, { method: "DELETE" });
        }
        catch {
            console.warn("Deleted locally without backend sync.");
        }
        categories = categories.filter(c => c.categoryID !== categoryId);
        render();
    }
    // Render UI Components
    function render(filteredItems = categories) {
        itemCountLabel.textContent = `${filteredItems.length} Categories`;
        // Render Horizontal Chips
        chipsContainer.innerHTML = filteredItems.map(item => `
      <div class="chip-item">
        <div class="chip-icon-box">${item.categoryName.charAt(0).toUpperCase()}</div>
        <span class="chip-label">${item.categoryName}</span>
      </div>
    `).join("");
        // Render Manage List
        listContainer.innerHTML = filteredItems.map(item => `
      <div class="category-card">
        <div>
          <div class="card-title">${item.categoryName}</div>
          <div class="card-subtitle">ID: ${item.categoryID}</div>
        </div>
        <div class="card-actions">
          <button class="btn-small btn-edit" onclick="openEditModal('${item.categoryID}')">Edit</button>
          <button class="btn-small btn-delete" onclick="deleteCategory('${item.categoryID}')">Delete</button>
        </div>
      </div>
    `).join("");
    }
    // Modal Handlers
    function openAddModal() {
        isEditMode = false;
        modalTitle.textContent = "Add Category";
        categoryIDInput.value = "";
        categoryIDInput.disabled = false;
        categoryNameInput.value = "";
        modalBackdrop.classList.add("open");
    }
    function openEditModal(categoryId) {
        const item = categories.find(c => c.categoryID === categoryId);
        if (!item)
            return;
        isEditMode = true;
        modalTitle.textContent = "Edit Category";
        categoryIDInput.value = item.categoryID;
        categoryIDInput.disabled = true; // Primary key should not change during update
        categoryNameInput.value = item.categoryName;
        modalBackdrop.classList.add("open");
    }
    function closeModal() {
        modalBackdrop.classList.remove("open");
    }
    // Event Listeners
    document.getElementById("openAddModalBtn").addEventListener("click", openAddModal);
    document.getElementById("quickAddBtn").addEventListener("click", openAddModal);
    document.getElementById("cancelModalBtn").addEventListener("click", closeModal);
    categoryForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        const payload = {
            categoryID: categoryIDInput.value.trim(),
            categoryName: categoryNameInput.value.trim()
        };
        if (isEditMode) {
            await updateCategory(payload);
        }
        else {
            await createCategory(payload);
        }
        closeModal();
        render();
    });
    searchInput.addEventListener("input", (e) => {
        const query = e.target.value.toLowerCase();
        const filtered = categories.filter(c => c.categoryName.toLowerCase().includes(query) || c.categoryID.toLowerCase().includes(query));
        render(filtered);
    });
    // Initial boot
    fetchCategories();
}
initializeCategoryPrototype();
// Original Frontend.zip module: Listing frontend/listing.js
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
// Original Frontend.zip module: Malik_Cart_CartItem 4/cart.js
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
// Original Frontend.zip module: Payment Frontend/payment.js
function initializePaymentPrototype() {
    if (!document.getElementById("checkoutView"))
        return;
    const API_URL = "http://localhost:8080/api/payment";
    let payments = [
        {
            paymentID: "PAY-1001",
            amount: 450.00,
            paymentMethod: "Card Payment",
            paymentStatus: "Completed",
            paymentDate: new Date().toISOString()
        }
    ];
    // Views
    const checkoutView = document.getElementById("checkoutView");
    const successView = document.getElementById("successView");
    const historyView = document.getElementById("historyView");
    // Form & Controls
    const paymentForm = document.getElementById("paymentForm");
    const cardFields = document.getElementById("cardFields");
    const paymentListContainer = document.getElementById("paymentListContainer");
    const historyCount = document.getElementById("historyCount");
    // Toggle card input display based on payment method selection
    document.querySelectorAll('input[name="paymentMethod"]').forEach((radio) => {
        radio.addEventListener("change", (e) => {
            if (e.target.value === "Card Payment") {
                cardFields.style.display = "block";
            }
            else {
                cardFields.style.display = "none";
            }
        });
    });
    // Navigation view switchers
    function showView(viewElement) {
        [checkoutView, successView, historyView].forEach(v => v.classList.remove("active"));
        viewElement.classList.add("active");
    }
    document.getElementById("viewHistoryBtn").addEventListener("click", () => {
        fetchPayments();
        showView(historyView);
    });
    document.getElementById("backToCheckoutBtn").addEventListener("click", () => {
        showView(checkoutView);
    });
    document.getElementById("viewHistoryFromSuccessBtn").addEventListener("click", () => {
        fetchPayments();
        showView(historyView);
    });
    document.getElementById("continueShoppingBtn").addEventListener("click", () => {
        showView(checkoutView);
    });
    document.getElementById("refreshPaymentsBtn").addEventListener("click", () => {
        fetchPayments();
    });
    // GET /api/payment
    async function fetchPayments() {
        try {
            const res = await fetch(API_URL);
            if (res.ok) {
                payments = await res.json();
            }
        }
        catch (err) {
            console.warn("Spring Boot backend offline; using local mock records.");
        }
        renderHistory();
    }
    // POST /api/payment
    async function createPayment(paymentPayload) {
        try {
            const res = await fetch(API_URL, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(paymentPayload)
            });
            if (res.ok) {
                const saved = await res.json();
                payments.unshift(saved);
                return saved;
            }
        }
        catch (err) {
            console.warn("Backend error, storing payment locally.");
        }
        payments.unshift(paymentPayload);
        return paymentPayload;
    }
    // DELETE /api/payment/{paymentId}
    async function deletePayment(paymentId) {
        try {
            await fetch(`${API_URL}/${paymentId}`, { method: "DELETE" });
        }
        catch (err) {
            console.warn("Backend offline; deleting from local array.");
        }
        payments = payments.filter(p => p.paymentID !== paymentId);
        renderHistory();
    }
    // Render Transaction History
    function renderHistory() {
        historyCount.textContent = payments.length;
        paymentListContainer.innerHTML = payments.map(p => {
            const dateFormatted = p.paymentDate ? new Date(p.paymentDate).toLocaleDateString() : "Just now";
            return `
        <div class="payment-item-card">
          <div class="payment-item-top">
            <span class="payment-item-id">#${p.paymentID}</span>
            <span class="payment-item-amount">R${Number(p.amount).toFixed(2)}</span>
          </div>
          <div class="payment-item-bottom">
            <span>${p.paymentMethod} &bull; ${dateFormatted}</span>
            <button class="btn-delete-payment" onclick="deletePayment('${p.paymentID}')">Delete</button>
          </div>
        </div>
      `;
        }).join("");
    }
    // Handle Checkout Form Submission
    paymentForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        const selectedMethod = document.querySelector('input[name="paymentMethod"]:checked').value;
        const newPayment = {
            paymentID: "PAY-" + Math.floor(100000 + Math.random() * 900000),
            amount: 450.00,
            paymentMethod: selectedMethod,
            paymentStatus: "Completed",
            paymentDate: new Date().toISOString()
        };
        const confirmedPayment = await createPayment(newPayment);
        // Populate Success Screen (Wireframe 4)
        document.getElementById("receiptPaymentID").textContent = confirmedPayment.paymentID;
        document.getElementById("receiptAmount").textContent = "R" + Number(confirmedPayment.amount).toFixed(2);
        document.getElementById("receiptMethod").textContent = confirmedPayment.paymentMethod;
        document.getElementById("receiptStatus").textContent = confirmedPayment.paymentStatus;
        document.getElementById("receiptDate").textContent = new Date(confirmedPayment.paymentDate).toLocaleString();
        showView(successView);
    });
    // Initial boot
    fetchPayments();
}
initializePaymentPrototype();
// Original Frontend.zip module: Project3_Order/script.js
function initializeOrdersPrototype() {
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.querySelector('.search-bar input');
        if (!searchInput)
            return;
        // Matches either a full order card (orders.html) or a single item card (orderitem.html)
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
            // Only orders.html has a dedicated order-id heading
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
// CommonCents PHP page interactions
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


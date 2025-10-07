
class ShoppingCart {
    constructor() {
        this.items = [];
        this.isOpen = false;
        this.boundClickHandler = null;
        this.init();
    }

    // Initialize cart system
    init() {
        this.loadFromStorage();
        this.createCartUI();
        this.bindEvents();
        this.updateDisplay();
    }

    // Add item to cart or increase quantity
    addItem(productId, name, price, image = '', prepTime = '') {
        const existingItem = this.items.find(item => item.id === productId);
        
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            this.items.push({
                id: productId,
                name,
                price: parseFloat(price),
                image,
                prepTime,
                quantity: 1
            });
        }
        
        this.save();
        this.updateDisplay();
        this.showNotification(`Added "${name}" to cart!`, 'success');
    }

    // Remove item from cart
    removeItem(productId) {
        this.items = this.items.filter(item => item.id !== productId);
        this.save();
        this.updateDisplay();
    }

    // Increase item quantity
    increaseQuantity(productId) {
        const item = this.items.find(item => item.id === productId);
        if (item) {
            item.quantity += 1;
            this.save();
            this.updateDisplay();
        }
    }

    // Decrease item quantity
    decreaseQuantity(productId) {
        const item = this.items.find(item => item.id === productId);
        if (item) {
            if (item.quantity > 1) {
                item.quantity -= 1;
                this.save();
                this.updateDisplay();
            } else {
                // If quantity is 1, remove the item entirely
                this.removeItem(productId);
            }
        }
    }

    // Clear all items from cart
    clearCart() {
        this.items = [];
        this.save();
        this.updateDisplay();
    }

    // Get total number of items
    getTotalItems() {
        return this.items.reduce((total, item) => total + item.quantity, 0);
    }

    // Get total cost
    getTotalCost() {
        return this.items.reduce((total, item) => total + (item.price * item.quantity), 0);
    }

    // Storage methods
    save() {
        localStorage.setItem('delicious_eats_cart', JSON.stringify(this.items));
    }

    loadFromStorage() {
        const stored = localStorage.getItem('delicious_eats_cart');
        if (stored) {
            this.items = JSON.parse(stored);
        }
    }

    // Create cart UI elements
    createCartUI() {
        this.createFloatingIcon();
        this.createSidebar();
        this.createOverlay();
        this.cacheElements();
    }

    createFloatingIcon() {
        const cartIcon = document.createElement('div');
        cartIcon.className = 'cart-icon';
        cartIcon.innerHTML = `
            <i class="fas fa-shopping-cart"></i>
            <span class="cart-counter">0</span>
        `;
        document.body.appendChild(cartIcon);
        this.cartIcon = cartIcon;
    }

    createSidebar() {
        const cartSidebar = document.createElement('div');
        cartSidebar.className = 'cart-sidebar';
        cartSidebar.innerHTML = `
            <div class="cart-header">
                <h3><i class="fas fa-shopping-cart"></i> Your Cart</h3>
                <button class="cart-close"><i class="fas fa-times"></i></button>
            </div>
            <div class="cart-items"></div>
            <div class="cart-footer">
                <div class="cart-total">
                    <strong>Total: $<span class="total-amount">0.00</span></strong>
                </div>
                <div class="cart-actions">
                    <button class="btn btn-clear">Clear Cart</button>
                    <button class="btn btn-checkout">Checkout</button>
                </div>
            </div>
        `;
        document.body.appendChild(cartSidebar);
        this.cartSidebar = cartSidebar;
    }

    createOverlay() {
        const cartOverlay = document.createElement('div');
        cartOverlay.className = 'cart-overlay';
        document.body.appendChild(cartOverlay);
        this.cartOverlay = cartOverlay;
    }

    cacheElements() {
        this.cartCounter = this.cartIcon.querySelector('.cart-counter');
        this.cartItems = this.cartSidebar.querySelector('.cart-items');
        this.totalAmount = this.cartSidebar.querySelector('.total-amount');
    }

    // Event binding
    bindEvents() {
        // Cart icon click to toggle
        this.cartIcon.addEventListener('click', () => this.toggleCart());
        
        // Close cart events
        this.cartSidebar.querySelector('.cart-close').addEventListener('click', () => this.closeCart());
        this.cartOverlay.addEventListener('click', () => this.closeCart());
        
        // Cart action buttons
        this.cartSidebar.querySelector('.btn-clear').addEventListener('click', () => this.handleClearCart());
        this.cartSidebar.querySelector('.btn-checkout').addEventListener('click', () => this.handleCheckout());

        // Add to cart buttons (delegated event)
        document.addEventListener('click', (e) => {
            if (e.target.matches('.btn[data-menu-id], .btn[data-menu-id] *')) {
                e.preventDefault();
                const button = e.target.closest('.btn[data-menu-id]');
                if (button) this.handleAddToCart(button);
            }
        });

        // Remove item buttons (delegated event)
        this.cartItems.addEventListener('click', (e) => {
            const productId = e.target.getAttribute('data-id');
            if (productId && e.target.matches('.remove-btn, .remove-btn *')) {
                this.removeItem(productId);
            }
        });
    }

    // Handle add to cart button clicks
    handleAddToCart(button) {
        const productCard = button.closest('.product-card');
        const productId = button.getAttribute('data-menu-id');
        const name = productCard.querySelector('h3').textContent;
        const priceText = productCard.querySelector('.price').textContent;
        const price = parseFloat(priceText.replace('$', ''));
        const image = productCard.querySelector('img').src;
        const prepTimeElement = productCard.querySelector('.prep-time');
        const prepTime = prepTimeElement ? prepTimeElement.textContent : '';

        this.addItem(productId, name, price, image, prepTime);
    }

    updateDisplay() {
        this.updateCounter();
        this.updateTotal();
        this.updateItemsList();
    }

    updateCounter() {
        const totalItems = this.getTotalItems();
        this.cartCounter.textContent = totalItems;
        
        if (totalItems > 0) {
            this.cartCounter.classList.add('show');
        } else {
            this.cartCounter.classList.remove('show');
        }
    }

    updateTotal() {
        this.totalAmount.textContent = this.getTotalCost().toFixed(2);
    }

    updateItemsList() {
        if (this.items.length === 0) {
            this.cartItems.innerHTML = `
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <p>Your cart is empty</p>
                    <p>Start adding some delicious items!</p>
                </div>
            `;
            return;
        }

        this.cartItems.innerHTML = this.items.map(item => `
            <div class="cart-item" data-id="${item.id}">
                <img src="${item.image}" alt="${item.name}" class="cart-item-image">
                <div class="cart-item-details">
                    <h4>${item.name}</h4>
                    ${item.prepTime ? `<p class="prep-time">${item.prepTime}</p>` : ''}
                    <p class="item-price">$${item.price.toFixed(2)} each</p>
                    <div class="quantity-controls">
                        <button class="quantity-btn decrease-btn" data-id="${item.id}">
                            <i class="fas fa-minus"></i>
                        </button>
                        <span class="quantity-display">${item.quantity}</span>
                        <button class="quantity-btn increase-btn" data-id="${item.id}">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="cart-item-controls">
                    <button class="remove-btn" data-id="${item.id}">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="item-total">$${(item.price * item.quantity).toFixed(2)}</div>
            </div>
        `).join('');
        
        // Bind quantity controls
        this.bindQuantityControls();
    }

    /**
     * Update cart items list
     */
    updateCartItems() {
        if (this.items.length === 0) {
            this.cartItems.innerHTML = `
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <p>Your cart is empty</p>
                    <p>Start adding some delicious items!</p>
                </div>
            `;
            return;
        }

        this.cartItems.innerHTML = this.items.map(item => `
            <div class="cart-item" data-id="${item.id}">
                <img src="${item.image}" alt="${item.name}" class="cart-item-image">
                <div class="cart-item-details">
                    <h4>${item.name}</h4>
                    ${item.prepTime ? `<p class="prep-time">${item.prepTime}</p>` : ''}
                    <p class="item-price">$${item.price.toFixed(2)} each</p>
                    <div class="quantity-controls">
                        <button class="quantity-btn decrease-btn" data-id="${item.id}">
                            <i class="fas fa-minus"></i>
                        </button>
                        <span class="quantity-display">${item.quantity}</span>
                        <button class="quantity-btn increase-btn" data-id="${item.id}">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="cart-item-controls">
                    <button class="remove-btn" data-id="${item.id}">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="item-total">$${(item.price * item.quantity).toFixed(2)}</div>
            </div>
        `).join('');

        // Bind quantity controls
        this.bindQuantityControls();
    }

    bindQuantityControls() {
        // Remove existing listener if it exists
        if (this.boundClickHandler && this.cartItems) {
            this.cartItems.removeEventListener('click', this.boundClickHandler);
        }
        
        // Create new bound click handler
        this.boundClickHandler = (e) => {
            // Find the button element (in case icon was clicked)
            const button = e.target.closest('button');
            if (!button) return;
            
            const productId = button.getAttribute('data-id');
            if (!productId) return;
            
            if (button.classList.contains('remove-btn')) {
                this.removeItem(productId);
            } else if (button.classList.contains('increase-btn')) {
                this.increaseQuantity(productId);
            } else if (button.classList.contains('decrease-btn')) {
                this.decreaseQuantity(productId);
            }
        };
        
        // Add the new listener
        if (this.cartItems) {
            this.cartItems.addEventListener('click', this.boundClickHandler);
        }
    }

    toggleCart() {
        this.isOpen ? this.closeCart() : this.openCart();
    }

    openCart() {
        this.isOpen = true;
        this.cartSidebar.classList.add('open');
        this.cartOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    closeCart() {
        this.isOpen = false;
        this.cartSidebar.classList.remove('open');
        this.cartOverlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    /**
     * Show animation when item is added
     */
    showAddedAnimation(itemName) {
        this.showNotification(`Added "${itemName}" to cart!`, 'success');

        // Animate cart icon (works for both header and floating)
        this.cartIcon.classList.add('bounce');
        setTimeout(() => this.cartIcon.classList.remove('bounce'), 600);
        
        // Pulse the counter if it's a header counter
        if (this.cartCounter.classList.contains('header-cart-counter')) {
            this.cartCounter.style.animation = 'pulse 0.3s ease';
            setTimeout(() => this.cartCounter.style.animation = '', 300);
        }
    }

    // Simple cart icon animation
    animateCartIcon() {
        if (this.cartIcon) {
            this.cartIcon.style.transform = 'scale(1.2)';
            setTimeout(() => {
                this.cartIcon.style.transform = 'scale(1)';
            }, 200);
        }
    }

    /**
     * Show notification with consistent styling
     */
    showNotification(message, type = 'success', duration = 2000) {
        const styles = {
            success: { icon: 'fas fa-check-circle', bg: 'linear-gradient(135deg, #28a745, #20c997)' },
            warning: { icon: 'fas fa-exclamation-triangle', bg: 'linear-gradient(135deg, #ffc107, #fd7e14)' },
            error: { icon: 'fas fa-times-circle', bg: 'linear-gradient(135deg, #dc3545, #c82333)' },
            info: { icon: 'fas fa-info-circle', bg: 'linear-gradient(135deg, #17a2b8, #138496)' }
        };

        const style = styles[type] || styles.success;
        const notification = document.createElement('div');
        notification.className = 'cart-notification';
        notification.style.background = style.bg;
        notification.innerHTML = `<i class="${style.icon}"></i><span>${message}</span>`;

        document.body.appendChild(notification);
        setTimeout(() => notification.classList.add('show'), 100);
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, duration);
    }

    handleCheckout() {
        if (this.items.length === 0) {
            this.showNotification('Your cart is empty!', 'warning');
            return;
        }

        const total = this.getTotalCost();
        const itemCount = this.getTotalItems();
        
        // Show customer information form
        Swal.fire({
            title: 'Customer Information',
            html: `
                <div class="customer-form-container">
                    <form id="customerForm" class="customer-form">
                        <div class="form-section">
                            <h6 class="section-title"><i class="fas fa-user"></i> Personal Information</h6>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="firstName">First Name *</label>
                                    <input type="text" class="form-control" id="firstName" placeholder="Enter first name" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="lastName">Last Name *</label>
                                    <input type="text" class="form-control" id="lastName" placeholder="Enter last name" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="email">Email Address *</label>
                                    <input type="email" class="form-control" id="email" placeholder="Enter email address" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="phone">Phone Number *</label>
                                    <input type="tel" class="form-control" id="phone" placeholder="Enter phone number" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h6 class="section-title"><i class="fas fa-map-marker-alt"></i> Delivery Information</h6>
                            <div class="form-group">
                                <label for="address">Delivery Address *</label>
                                <textarea class="form-control" id="address" placeholder="Enter complete delivery address" rows="2" required></textarea>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="city">City</label>
                                    <input type="text" class="form-control" id="city" placeholder="Enter city">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="zipCode">ZIP Code</label>
                                    <input type="text" class="form-control" id="zipCode" placeholder="Enter ZIP code">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h6 class="section-title"><i class="fas fa-credit-card"></i> Payment & Notes</h6>
                            <div class="form-group">
                                <label for="paymentMethod">Payment Method</label>
                                <select class="form-control" id="paymentMethod">
                                    <option value="cash">💵 Cash on Delivery</option>
                                    <option value="credit_card">💳 Credit Card</option>
                                    <option value="debit_card">💳 Debit Card</option>
                                    <option value="online">🌐 Online Payment</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="specialInstructions">Special Instructions</label>
                                <textarea class="form-control" id="specialInstructions" placeholder="Any special requests or delivery instructions..." rows="2"></textarea>
                            </div>
                        </div>
                    </form>
                    
                    <div class="order-summary">
                        <h6 class="summary-title"><i class="fas fa-receipt"></i> Order Summary</h6>
                        <div class="summary-row">
                            <span><i class="fas fa-box text-primary"></i> Items:</span>
                            <span class="summary-value">${itemCount}</span>
                        </div>
                        <div class="summary-row total-row">
                            <span><i class="fas fa-dollar-sign text-success"></i> Total:</span>
                            <span class="summary-value total-amount">$${total.toFixed(2)}</span>
                        </div>
                    </div>
                </div>
                
                <style>
                    .customer-form-container {
                        text-align: left;
                        max-height: 70vh;
                        overflow-y: auto;
                        padding: 10px;
                    }
                    
                    .customer-form {
                        margin-bottom: 20px;
                    }
                    
                    .form-section {
                        margin-bottom: 25px;
                        padding: 15px;
                        border: 1px solid #e9ecef;
                        border-radius: 8px;
                        background-color: #f8f9fa;
                    }
                    
                    .section-title {
                        color: #495057;
                        margin-bottom: 15px;
                        font-weight: 600;
                        border-bottom: 2px solid #dee2e6;
                        padding-bottom: 8px;
                    }
                    
                    .form-row {
                        display: flex;
                        gap: 15px;
                        margin-bottom: 15px;
                    }
                    
                    .form-group {
                        flex: 1;
                        margin-bottom: 15px;
                    }
                    
                    .form-group label {
                        font-weight: 500;
                        color: #495057;
                        margin-bottom: 5px;
                        display: block;
                    }
                    
                    .form-control {
                        width: 100%;
                        padding: 10px 12px;
                        border: 1px solid #ced4da;
                        border-radius: 6px;
                        font-size: 14px;
                        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
                    }
                    
                    .form-control:focus {
                        border-color: #80bdff;
                        outline: 0;
                        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
                    }
                    
                    .order-summary {
                        background: linear-gradient(135deg, #28a745, #20c997);
                        color: white;
                        padding: 20px;
                        border-radius: 10px;
                        margin-top: 20px;
                    }
                    
                    .summary-title {
                        color: white;
                        margin-bottom: 15px;
                        font-weight: 600;
                        text-align: center;
                    }
                    
                    .summary-row {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        margin-bottom: 8px;
                        padding: 5px 0;
                    }
                    
                    .total-row {
                        border-top: 2px solid rgba(255, 255, 255, 0.3);
                        margin-top: 10px;
                        padding-top: 10px;
                        font-weight: 600;
                        font-size: 1.1em;
                    }
                    
                    .summary-value {
                        font-weight: 600;
                    }
                    
                    .total-amount {
                        font-size: 1.2em;
                        color: #fff3cd;
                    }
                    
                    @media (max-width: 768px) {
                        .form-row {
                            flex-direction: column;
                            gap: 0;
                        }
                        
                        .customer-form-container {
                            max-height: 80vh;
                        }
                    }
                </style>
            `,
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-credit-card"></i> Place Order',
            cancelButtonText: '<i class="fas fa-times"></i> Cancel',
            reverseButtons: true,
            width: '700px',
            customClass: {
                popup: 'customer-info-modal',
                confirmButton: 'btn-place-order',
                cancelButton: 'btn-cancel-order'
            },
            preConfirm: () => {
                const form = document.getElementById('customerForm');
                const formData = new FormData(form);
                
                // Validate required fields
                const firstName = document.getElementById('firstName').value.trim();
                const lastName = document.getElementById('lastName').value.trim();
                const email = document.getElementById('email').value.trim();
                const phone = document.getElementById('phone').value.trim();
                const address = document.getElementById('address').value.trim();
                
                if (!firstName || !lastName || !email || !phone || !address) {
                    Swal.showValidationMessage('Please fill in all required fields');
                    return false;
                }
                
                // Email validation
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    Swal.showValidationMessage('Please enter a valid email address');
                    return false;
                }
                
                return {
                    first_name: firstName,
                    last_name: lastName,
                    email: email,
                    phone: phone,
                    address: address,
                    city: document.getElementById('city').value.trim(),
                    zip_code: document.getElementById('zipCode').value.trim(),
                    payment_method: document.getElementById('paymentMethod').value,
                    special_instructions: document.getElementById('specialInstructions').value.trim()
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                this.submitOrder(result.value);
            }
        });
    }

    async submitOrder(customerInfo) {
        try {
            // Show loading
            Swal.fire({
                title: 'Processing Order...',
                text: 'Please wait while we process your order',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            const orderData = {
                items: this.items,
                customer_info: customerInfo
            };

            const response = await fetch('../api/process_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(orderData)
            });

            const result = await response.json();

            if (result.success) {
                // Order successful
                Swal.fire({
                    title: 'Order Placed Successfully!',
                    html: `
                        <div class="text-start">
                            <p><strong>Thank you for your order!</strong></p>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span><i class="fas fa-receipt text-primary"></i> Order #:</span>
                                <span><strong>${result.order_id}</strong></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span><i class="fas fa-dollar-sign text-success"></i> Total:</span>
                                <span><strong>$${result.total_amount.toFixed(2)}</strong></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span><i class="fas fa-clock text-info"></i> Est. Delivery:</span>
                                <span><strong>${new Date(result.estimated_delivery).toLocaleTimeString()}</strong></span>
                            </div>
                            <hr>
                            <p class="text-info mb-0">
                                <i class="fas fa-phone"></i> You will receive a confirmation call shortly.
                            </p>
                        </div>
                    `,
                    icon: 'success',
                    confirmButtonColor: '#28a745',
                    confirmButtonText: '<i class="fas fa-check"></i> Great!'
                });

                // Clear cart and close
                this.clearCart();
                this.closeCart();
                
            } else {
                // Order failed
                Swal.fire({
                    title: 'Order Failed',
                    text: result.message || 'There was an error processing your order. Please try again.',
                    icon: 'error',
                    confirmButtonColor: '#dc3545'
                });
            }

        } catch (error) {
            console.error('Order submission error:', error);
            Swal.fire({
                title: 'Network Error',
                text: 'Unable to submit order. Please check your connection and try again.',
                icon: 'error',
                confirmButtonColor: '#dc3545'
            });
        }
    }

    handleClearCart() {
        if (this.items.length === 0) {
            this.showNotification('Your cart is already empty!', 'info');
            return;
        }

        const itemCount = this.getTotalItems();
        const total = this.getTotalCost();
        
        Swal.fire({
            title: 'Clear Cart?',
            html: `
                <div class="text-start">
                    <p><strong>Are you sure you want to clear your cart?</strong></p>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span><i class="fas fa-box text-primary"></i> Items:</span>
                        <span><strong>${itemCount}</strong></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span><i class="fas fa-dollar-sign text-success"></i> Total:</span>
                        <span><strong>$${total.toFixed(2)}</strong></span>
                    </div>
                    <hr>
                    <p class="text-danger mb-0">
                        <i class="fas fa-exclamation-triangle"></i> This action cannot be undone!
                    </p>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash"></i> Yes, clear it!',
            cancelButtonText: '<i class="fas fa-times"></i> Cancel',
            reverseButtons: true,
            customClass: {
                popup: 'swal-wide'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                this.clearCart();
                this.showNotification('🗑️ Cart cleared successfully! All items have been removed.', 'info');
            }
        });
    }
}

// Initialize cart when page loads
document.addEventListener('DOMContentLoaded', function() {
    window.cart = new ShoppingCart();
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ShoppingCart;
}
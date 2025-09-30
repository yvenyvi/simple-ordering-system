/**
 * Shopping Cart Management System
 * Handles cart operations, UI interactions, and persistence
 */
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
        
        Swal.fire({
            title: 'Checkout Confirmation',
            html: `
                <div class="text-start">
                    <p><strong>Ready to place your order?</strong></p>
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
                    <p class="text-info mb-0">
                        <i class="fas fa-phone"></i> You will receive a confirmation call shortly.
                    </p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-credit-card"></i> Place Order',
            cancelButtonText: '<i class="fas fa-times"></i> Cancel',
            reverseButtons: true,
            customClass: {
                popup: 'swal-wide'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Simulate checkout process
                this.showNotification(`🎉 Order Successful! ${itemCount} items for $${total.toFixed(2)}. You will receive a confirmation call shortly!`, 'success', 3000);
                
                this.clearCart();
                this.closeCart();
            }
        });
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
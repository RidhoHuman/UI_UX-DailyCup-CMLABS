/**
 * Shopping Cart JavaScript
 */

// Cart data
let cart = [];

// Initialize cart from session storage
document.addEventListener('DOMContentLoaded', function() {
    loadCart();
    updateCartDisplay();
    initCartEvents();
});

/**
 * Load cart from server
 */
function loadCart() {
    fetch('/dailycup/api/cart.php?action=get')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                cart = data.cart || [];
                updateCartDisplay();
            }
        })
        .catch(error => console.error('Error loading cart:', error));
}

/**
 * Add item to cart
 */
function addToCart(productId, productName, price, size = null, temperature = null, quantity = 1) {
    const data = {
        action: 'add',
        product_id: productId,
        product_name: productName,
        price: price,
        size: size,
        temperature: temperature,
        quantity: quantity
    };
    
    fetch('/dailycup/api/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            cart = data.cart;
            updateCartDisplay();
            showCartModal();
            if (window.DailyCup) {
                window.DailyCup.showAlert('Produk ditambahkan ke keranjang!', 'success');
            }
        } else {
            if (window.DailyCup) {
                window.DailyCup.showAlert(data.message || 'Gagal menambahkan ke keranjang', 'danger');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.DailyCup) {
            window.DailyCup.showAlert('Terjadi kesalahan', 'danger');
        }
    });
}

/**
 * Update cart item quantity
 */
function updateCartItem(cartKey, quantity) {
    if (quantity < 1) {
        removeFromCart(cartKey);
        return;
    }
    
    const data = {
        action: 'update',
        cart_key: cartKey,
        quantity: quantity
    };
    
    fetch('/dailycup/api/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            cart = data.cart;
            updateCartDisplay();
        }
    })
    .catch(error => console.error('Error:', error));
}

/**
 * Remove item from cart
 */
function removeFromCart(cartKey) {
    if (!confirm('Hapus item dari keranjang?')) {
        return;
    }
    
    const data = {
        action: 'remove',
        cart_key: cartKey
    };
    
    fetch('/dailycup/api/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            cart = data.cart;
            updateCartDisplay();
            if (window.DailyCup) {
                window.DailyCup.showAlert('Item dihapus dari keranjang', 'info');
            }
        }
    })
    .catch(error => console.error('Error:', error));
}

/**
 * Clear entire cart
 */
function clearCart() {
    if (!confirm('Hapus semua item dari keranjang?')) {
        return;
    }
    
    fetch('/dailycup/api/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ action: 'clear' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            cart = [];
            updateCartDisplay();
            if (window.DailyCup) {
                window.DailyCup.showAlert('Keranjang dikosongkan', 'info');
            }
        }
    })
    .catch(error => console.error('Error:', error));
}

/**
 * Update cart display
 */
function updateCartDisplay() {
    // Update cart count in navbar
    const cartCountElements = document.querySelectorAll('.cart-count');
    const cartCount = cart.length;
    
    cartCountElements.forEach(element => {
        element.textContent = cartCount;
        element.style.display = cartCount > 0 ? 'flex' : 'none';
    });
    
    // Update cart total
    const total = calculateCartTotal();
    const cartTotalElements = document.querySelectorAll('.cart-total');
    cartTotalElements.forEach(element => {
        element.textContent = formatCurrency(total);
    });
    
    // Update cart items list if on cart page
    const cartItemsContainer = document.getElementById('cartItems');
    if (cartItemsContainer) {
        if (cart.length === 0) {
            cartItemsContainer.innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-cart-x" style="font-size: 4rem; color: #ccc;"></i>
                    <p class="mt-3 text-muted">Keranjang Anda kosong</p>
                    <a href="/dailycup/customer/menu.php" class="btn btn-coffee">Belanja Sekarang</a>
                </div>
            `;
        } else {
            let html = '';
            cart.forEach((item, index) => {
                html += `
                    <div class="cart-item border-bottom py-3">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <img src="/dailycup/assets/images/products/placeholder.jpg" 
                                     class="img-fluid rounded" alt="${item.product_name}">
                            </div>
                            <div class="col-md-4">
                                <h6 class="mb-1">${item.product_name}</h6>
                                ${item.size ? `<small class="text-muted">Size: ${item.size}</small><br>` : ''}
                                ${item.temperature ? `<small class="text-muted">Temp: ${item.temperature}</small>` : ''}
                            </div>
                            <div class="col-md-2">
                                <p class="mb-0 fw-bold">${formatCurrency(item.price)}</p>
                            </div>
                            <div class="col-md-2">
                                <div class="quantity-input">
                                    <button onclick="updateCartItem('${index}', ${item.quantity - 1})" class="btn btn-sm">-</button>
                                    <input type="number" value="${item.quantity}" min="1" 
                                           onchange="updateCartItem('${index}', this.value)" readonly>
                                    <button onclick="updateCartItem('${index}', ${item.quantity + 1})" class="btn btn-sm">+</button>
                                </div>
                            </div>
                            <div class="col-md-2 text-end">
                                <button onclick="removeFromCart('${index}')" class="btn btn-danger btn-sm">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
            cartItemsContainer.innerHTML = html;
        }
    }
}

/**
 * Calculate cart total
 */
function calculateCartTotal() {
    return cart.reduce((total, item) => {
        return total + (item.price * item.quantity);
    }, 0);
}

/**
 * Show cart modal (mini cart)
 */
function showCartModal() {
    // This can be implemented as a Bootstrap modal if needed
    // For now, we'll just update the display
    updateCartDisplay();
}

/**
 * Initialize cart events
 */
function initCartEvents() {
    // Add to cart buttons
    document.querySelectorAll('.add-to-cart-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            const productName = this.dataset.productName;
            const price = parseFloat(this.dataset.price);
            
            // Get size and temperature if available
            const sizeSelect = document.getElementById('size-' + productId);
            const tempSelect = document.getElementById('temperature-' + productId);
            const quantityInput = document.getElementById('quantity-' + productId);
            
            const size = sizeSelect ? sizeSelect.value : null;
            const temperature = tempSelect ? tempSelect.value : null;
            const quantity = quantityInput ? parseInt(quantityInput.value) : 1;
            
            addToCart(productId, productName, price, size, temperature, quantity);
        });
    });
}

/**
 * Apply discount code
 */
function applyDiscountCode(code) {
    if (!code) {
        if (window.DailyCup) {
            window.DailyCup.showAlert('Masukkan kode diskon', 'warning');
        }
        return;
    }
    
    fetch('/dailycup/api/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'apply_discount',
            code: code
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (window.DailyCup) {
                window.DailyCup.showAlert('Kode diskon berhasil digunakan!', 'success');
            }
            // Reload page to show updated prices
            location.reload();
        } else {
            if (window.DailyCup) {
                window.DailyCup.showAlert(data.message || 'Kode diskon tidak valid', 'danger');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.DailyCup) {
            window.DailyCup.showAlert('Terjadi kesalahan', 'danger');
        }
    });
}

/**
 * Format currency
 */
function formatCurrency(amount) {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
}

// Make functions available globally
window.addToCart = addToCart;
window.updateCartItem = updateCartItem;
window.removeFromCart = removeFromCart;
window.clearCart = clearCart;
window.applyDiscountCode = applyDiscountCode;

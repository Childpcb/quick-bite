// ============================================================
// QUICK BITE - CART JAVASCRIPT
// ============================================================

function addToCart(foodId, quantity = 1) {
    const isLoggedIn = typeof userId !== 'undefined' && userId > 0;
    
    if (!isLoggedIn) {
        window.location.href = '/quick-bite/login.php?redirect=' + window.location.pathname;
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('food_id', foodId);
    formData.append('quantity', quantity);
    
    fetch('/quick-bite/cart-action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update cart count
            updateCartCount();
            showNotification('Item added to cart!', 'success');
        } else {
            showNotification(data.message || 'Failed to add to cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Something went wrong. Please try again.', 'error');
    });
}

function updateCart(itemId, quantity) {
    if (quantity < 1) {
        removeFromCart(itemId);
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('item_id', itemId);
    formData.append('quantity', quantity);
    
    fetch('/quick-bite/cart-action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            showNotification(data.message || 'Failed to update cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Something went wrong. Please try again.', 'error');
    });
}

function removeFromCart(itemId) {
    if (!confirm('Are you sure you want to remove this item?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'remove');
    formData.append('item_id', itemId);
    
    fetch('/quick-bite/cart-action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            showNotification(data.message || 'Failed to remove item', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Something went wrong. Please try again.', 'error');
    });
}

function clearCart() {
    if (!confirm('Are you sure you want to clear your cart?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'clear');
    
    fetch('/quick-bite/cart-action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            showNotification(data.message || 'Failed to clear cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Something went wrong. Please try again.', 'error');
    });
}

function updateCartCount() {
    fetch('/quick-bite/cart-count.php')
        .then(response => response.json())
        .then(data => {
            const badges = document.querySelectorAll('.cart-badge');
            badges.forEach(badge => {
                if (data.count > 0) {
                    badge.textContent = data.count;
                    badge.style.display = 'inline';
                } else {
                    badge.style.display = 'none';
                }
            });
        })
        .catch(error => console.error('Error:', error));
}

function showNotification(message, type = 'success') {
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        warning: 'bg-yellow-500',
        info: 'bg-blue-500'
    };
    
    const notification = document.createElement('div');
    notification.className = `fixed top-24 right-4 ${colors[type] || colors.info} text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-transform transform`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}
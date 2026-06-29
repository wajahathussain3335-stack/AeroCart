// Dynamic Backend Cart Sync
const cartCountBadge = document.getElementById('cart-count');
const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');

addToCartButtons.forEach(button => {
    button.addEventListener('click', () => {
        const productId = button.getAttribute('data-id');

        // FormData object banana backend ko data bhejne ke liye
        let formData = new FormData();
        formData.append('ajax_add', '1');
        formData.append('product_id', productId);

        // PHP file ko fetch request bhejna (Bina page refresh kiye)
        fetch('cart_action.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if(data === "out_of_stock") {
                alert("Sorry, this item is out of stock!");
            } else {
                // Header ke badge counter ko update karna real dynamic numbers se
                cartCountBadge.innerText = data;

                // Visual effect
                button.innerText = "Added ✓";
                button.classList.remove('bg-gray-900');
                button.classList.add('bg-green-600');

                setTimeout(() => {
                    button.innerText = "Add to Cart";
                    button.classList.remove('bg-green-600');
                    button.classList.add('bg-gray-900');
                }, 1200);
            }
        })
        .catch(error => console.error('Error:', error));
    });
});
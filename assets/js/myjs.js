$(document).ready(function () {
    $('.increment-btn').click(function (e) { 
        e.preventDefault();
        var qty = $(this).closest('.perfume_data').find('.perfume-qty').val();
        //alert(qty);
        var value = parseInt(qty, 10);
        value = isNaN(value)? 0 : value;
        if (value < 10) {
            value++;
            $(this).closest('.perfume_data').find('.perfume-qty').val(value);
        }
    });

    $('.decrement-btn').click(function (e) { 
        e.preventDefault();
        var qty = $(this).closest('.perfume_data').find('.perfume-qty').val();
        //alert(qty);
        var value = parseInt(qty, 10);
        value = isNaN(value)? 0 : value;
        if (value > 1) {
            value--;
            $(this).closest('.perfume_data').find('.perfume-qty').val(value);
        }
    });

    $('.add_to_cart').click(function (e) { 
        e.preventDefault();
        var qty = $(this).closest('.perfume_data').find('.perfume-qty').val();
        var perfume_id = $(this).val();
        //alert(perfume_id);
        $.ajax({
            method: "POST",
            url: "functions/cart-function.php",
            data: {
                "perfume_id" : perfume_id,
                "perfume_qty" : qty,
                "scope" : "add"
            },
            //dataType: "dataType",
            success: function (response) {
                if (response == 201) {
                    alertify.success("Perfume successfully added to cart!");
                }
                else if (response == 401) {
                    alertify.error("Please login to continue.");
                }
                else if (response == 500) {
                    alertify.error("Something went wrong");
                }
                else if (response == 69) {
                    alertify.error("Item already exists in your cart");
                }
            }
        });
    });

    $(document).on('click','.updateQty', function () {
        var qty = $(this).closest('.perfume_data').find('.perfume-qty').val();
        var perfume_id = $(this).closest('.perfume_data').find('.perfumeID').val();
        
        $.ajax({
            type: "POST",
            url: "functions/cart-function.php",
            data: {
                "perfume_id" : perfume_id,
                "perfume_qty" : qty,
                "scope" : "update"
            },
            success: function (response) {
                if (response == 500) {
                    alertify.error("Something went wrong and we are not competent enough to know what's wrong.");
                }
                else if (response == 200) {
                    alertify.success("The quantity was successfully updated!");
                } 
                else if (response == 69) {
                    alertify.error("Item already exists in your cart");
                }
            }
        });
    });

    $(document).on('click', '.delete_cart_item', function () {
        var cart_id = $(this).val();
        $.ajax({
            type: "POST",
            url: "functions/cart-function.php",
            data: {
                "cart_id" : cart_id,
                //"perfume_qty" : qty,
                "scope" : "delete"
            },
            success: function (response) {
                if (response == 200) {
                    alertify.success("The perfume was successfuly removed from your cart!");
                    $('#shopcart').load(location.href + " #shopcart");
                }
                else if (response == 500) {
                    alertify.error("There was an issue removing it from your cart.");
                }
            }
        });
    });
});


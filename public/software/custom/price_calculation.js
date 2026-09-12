function updateInnerAndOuterFromQty(t) {
    var row = $(t).parent('td').parent('tr');
    var db_outer_quantity = parseFloat($(row).find("input.outer_quantity").val());
    var db_inner_quantity = parseFloat($(row).find("input.inner_quantity").val());
    var quantity = parseFloat($(row).find("td").find("input.quantity").val());
    // console.log(quantity);

    if(db_outer_quantity != 0 && db_inner_quantity != 0) {
        var outerCount = Math.floor(quantity / db_outer_quantity);
        var remaining = quantity % db_outer_quantity;
        var innerCount = Math.floor(remaining / db_inner_quantity);
        var looseCount = remaining % db_inner_quantity;
    } else if (db_outer_quantity == 0 && db_inner_quantity != 0) {
        innerCount = Math.floor(quantity / db_inner_quantity);
        looseCount = quantity % db_inner_quantity;
    } else if (db_outer_quantity != 0 && db_inner_quantity == 0) {
        outerCount = Math.floor(quantity / db_outer_quantity);
        looseCount = quantity % db_outer_quantity;
    } else {
        outerCount = 0;
        innerCount = 0;
        looseCount = quantity;
    }

    $(row).find("td").find("input.outer").val(outerCount);
    $(row).find("td").find("input.inner").val(innerCount);
    $(row).find("td").find("input.loose").val(looseCount);

    recalculateRow(t);
}

function changeQty(t, type) {
    var row = $(t).parent('td').parent('tr');
    var db_outer_quantity = parseFloat($(row).find("input.outer_quantity").val()) || 0;
    var db_inner_quantity = parseFloat($(row).find("input.inner_quantity").val()) || 0;

    var outer = parseFloat($(row).find("td").find("input.outer").val()) || 0;
    var inner = parseFloat($(row).find("td").find("input.inner").val()) || 0;
    var quantity = 0;

    if(type == 1) { // outer
        $(row).find("td").find("input.inner").val('');
        if (db_outer_quantity != 0) {
            quantity = outer * db_outer_quantity;
        } else {
            quantity = 0;
        }
    } else if(type == 2) { // inner
        $(row).find("td").find("input.outer").val('');
        if (db_inner_quantity != 0) {
            quantity = inner * db_inner_quantity;
        } else {
            quantity = 0;
        }
    }

    $(row).find("td").find("input.quantity").val(quantity);
    recalculateRow(t);
}

$('#is_gst_applicable').click(function() {
    recalculateRow();
});

$('#is_tcs_applicable').click(function() {
    recalculateRow();
});

function checkMax(input) {
    if (input.value > 100) {
        input.value = '';
        toastr.error("Discount percentage cannot be more than 100!");
    }
}

function validateAndRecalculate(input, row) {
    validateFlatDiscount(input);
    recalculateRow(input, row);
}

function validateFlatDiscount(input) {
    var price = $(input).closest('tr').find('.price').val();
    var discount = parseFloat(input.value);

    if (discount > parseFloat(price)) {
        toastr.error("Discount (Flat) cannot be more than Price (in INR)!");
        input.value = '';
    }
}

 $(document).on('change', '#additional_discount_amount, #cash_discount_percentage', function(e) {
    var total_taxable = parseFloat($("#total_taxable").val());
    if (!total_taxable || parseInt(total_taxable) <= 0) {
        toastr.error("Please Enter valid Discount Amount!");
        $("#additional_discount_amount").val('0');
        recalculateRow(e.target);
        return;
    }
});

function recalculateRow(t, discount_type = "") {
    var cash_discount_amount = parseFloat($("#cash_discount_amount").val()) || 0;
    var master_amount = parseFloat($("#master_amount").val()) || 0;
    var additional_discount_amount = parseFloat($("#additional_discount_amount").val()) || 0;

    if(cash_discount_amount > master_amount) {
        toastr.error("Cash Discount Amount cannot be more than Master Amount!");
        $("#cash_discount_amount").val('0');
        return;
    }

    if(additional_discount_amount > master_amount) {
        toastr.error("Additional Discount Amount cannot be more than Master Amount!");
        $("#additional_discount_amount").val('0');
        return;
    }

    var row = $(t).parent('td').parent('tr');
    var total_row = $('#productBody tr').length;

    if (discount_type == 1) {
        $(row).find("td").find("input.discount_percentage").val("");
    } else if (discount_type == 2) {
        $(row).find("td").find("input.discount_flat").val("");
    }

    var price = $(row).find("td").find("input.price").val();
    var quantity = $(row).find("td").find("input.quantity").val();
    var discount_flat = $(row).find("td").find("input.discount_flat").val();
    var discount_percentage = $(row).find("td").find("input.discount_percentage").val();
    var taxable_amount = $(row).find("td").find("input.taxable_amount").val();

    var master_quantity = $("#master_quantity").val();
    var master_amount = $("#master_amount").val();
    var master_taxable_amount = $("#master_taxable_amount").val();
    var master_gst_amount = $("#master_gst_amount").val();
    var master_sub_total = $("#master_sub_total").val();

    var cash_discount_percentage = $("#cash_discount_percentage").val();
    var additional_discount_percentage = $("#additional_discount_percentage").val();

    var packing_forwarding_charge = $("#packing_forwarding_charge").val();
    var transport = $("#transport").val();

    // if (quantity) {
    //     if (event.keyCode == 46 || event.keyCode == 8) {
    //     } else if (/\D/g.test(quantity)) {
    //         if (qty_allow_option == 0) {
    //             toastr.error("Only Digits Allowed");
    //             quantity = quantity.replace(/\D/g, '');
    //         }
    //     }
    // }
    if (quantity) {
        const isValidDecimal = /^(\d+(\.\d{0,2})?)?$/.test(quantity);

        if (!isValidDecimal) {
            if (qty_allow_option == 0) {
                toastr.error("Only numbers and one decimal point allowed!");
                quantity = quantity.replace(/[^0-9.]/g, "");

                // Ensure only one dot remains
                const parts = quantity.split(".");
                if (parts.length > 2) {
                    quantity = parts[0] + "." + parts[1];
                }
            }
        }
    }

    if(discount_flat < 0) {
        toastr.error("Minus Value Not Allowed!");
        $(row).find("td").find("input.discount_flat").val("");
        discount_flat = 0;
    }

    if(discount_percentage < 0) {
        toastr.error("Minus Value Not Allowed!");
        $(row).find("td").find("input.discount_percentage").val("");
        discount_percentage = 0;
    }

    if (isNaN(packing_forwarding_charge) || packing_forwarding_charge == "NaN" || packing_forwarding_charge == "") {
        packing_forwarding_charge = 0;
    }

    if (isNaN(transport) || transport == "NaN" || transport == "") {
        transport = 0;
    }

    if (isNaN(master_quantity) || master_quantity == "NaN" || master_quantity == "") {
        master_quantity = 0;
    }

    if (isNaN(master_amount) || master_amount == "NaN" || master_amount == "") {
        master_amount = 0;
    }

    if (isNaN(master_taxable_amount) || master_taxable_amount == "NaN" || master_taxable_amount == "") {
        master_taxable_amount = 0;
    }

    if (isNaN(master_gst_amount) || master_gst_amount == "NaN" || master_gst_amount == "") {
        master_gst_amount = 0;
    }

    if (isNaN(master_sub_total) || master_sub_total == "NaN" || master_sub_total == "") {
        master_sub_total = 0;
    }

    if (isNaN(quantity) || quantity == "NaN" || quantity == "") {
        quantity = 0;
    }

    if (isNaN(discount_flat) || discount_flat == "NaN" || discount_flat == "") {
        discount_flat = 0;
    }

    if (isNaN(discount_percentage) || discount_percentage == "NaN" || discount_percentage == "") {
        discount_percentage = 0;
    }

    if (isNaN(cash_discount_percentage) || cash_discount_percentage == "NaN" || cash_discount_percentage == "") {
        cash_discount_percentage = 0;
    }

    if (isNaN(additional_discount_percentage) || additional_discount_percentage == "NaN" || additional_discount_percentage == "") {
        additional_discount_percentage = 0;
    }

    if (isNaN(taxable_amount) || taxable_amount == "NaN" || taxable_amount == "") {
        taxable_amount = 0;
    }

    var per_product_packing_charge = parseFloat(packing_forwarding_charge / total_row);
    var per_transport_charge = parseFloat(transport / total_row);

    var amount = price * quantity;
    if (discount_flat != "" && discount_flat != 0) {
        var rate_in_amount = (parseFloat(price) - parseFloat(discount_flat));
    } else {
        var rate_in_amount = (parseFloat(price) * (1 - (parseFloat(discount_percentage) / 100)));
    }
    amount = parseFloat(rate_in_amount) * parseFloat(quantity);

    $(row).find("td").find("input.rate_in_amount").val(rate_in_amount.toFixed(2));
    $(row).find("td").find("input.amount").val(amount.toFixed(2));
    $(row).find("td").find("input.taxable_amount").val(amount.toFixed(2));

    if ($(t).attr('name') === 'kg[]') {
        const enteredKg = parseFloat($(t).val()); // entered in kg
        const enteredGrams = enteredKg * 1000; // convert kg to grams

        const $row = $(t).closest('tr');
        const $hiddenInput = $row.find('input.kg_quantity');

        let qtyValue = $('#qty_is_value').val();

        if (qtyValue.toLowerCase().includes('kg')) {
            const productWeightGrams = parseFloat($hiddenInput.val()); // e.g., 200g
            const priceForThatWeight = parseFloat($hiddenInput.attr('product_weight_price')); // price for 200g

            if (!isNaN(enteredGrams) && productWeightGrams > 0 && priceForThatWeight > 0) {
                const calculatedAmount = (enteredGrams / productWeightGrams) * priceForThatWeight;
                const calculatedquantity = parseFloat(enteredGrams / productWeightGrams);

                $row.find('input.amount').val(calculatedAmount.toFixed(2));
                $row.find('input.taxable_amount').val(calculatedAmount.toFixed(2));
                $row.find('input.quantity').val(calculatedquantity.toFixed(2));
            } else {
                $row.find('input.amount').val('0.00');
                $row.find('input.taxable_amount').val('0.00');
            }
        }
    }

    let totalQty = 0;
    $('input[name="quantity[]"]').each(function () {
        let quantity = parseFloat($(this).val()) || 0;
        totalQty += quantity;
    });

    let totalamount = 0;
    let total_taxable_amount = 0;
    let total_cash_discount_amount = 0;
    let total_additional_discount_amount = 0;

    $('input[name="amount[]"]').each(function (index) {
        let amount = parseFloat($(this).val()) || 0;
        let gst_in_percentage = $('input[name="gst_in_percentage[]"]').eq(index).val();

        var input_cash_discount_amount = $("#cash_discount_amount").val() || 0;
        var input_cash_discount_amount_per_product = parseFloat(input_cash_discount_amount) / total_row;

        var input_additional_discount_amount = $("#additional_discount_amount").val() || 0;
        var input_additional_discount_amount_per_product = parseFloat(input_additional_discount_amount) / total_row;

        totalamount += amount;
        let cash_discount_amount = (cash_discount_percentage !== 0) ? (amount * (cash_discount_percentage / 100)) : input_cash_discount_amount_per_product;
        let discounted_amount = amount - cash_discount_amount;
        total_cash_discount_amount += parseFloat(cash_discount_amount);

        let additional_discount_amount = (additional_discount_percentage !== 0) ? (discounted_amount * (additional_discount_percentage / 100)) : input_additional_discount_amount_per_product;
        let new_taxable_amount = discounted_amount - additional_discount_amount;
        total_additional_discount_amount += parseFloat(additional_discount_amount);

        new_taxable_amount += per_product_packing_charge + per_transport_charge;
        $('input[name="taxable_amount[]"]').eq(index).val(new_taxable_amount.toFixed(2));
        total_taxable_amount += new_taxable_amount;

        var gst_amount = new_taxable_amount * (parseFloat(gst_in_percentage) / 100);
        var sub_total = parseFloat(new_taxable_amount) + parseFloat(gst_amount);

        $('input[name="gst_amount_array[]"]').eq(index).val(gst_amount.toFixed(2));
        $('input[name="subtotal[]"]').eq(index).val(sub_total.toFixed(2));
    });

    let total_gst_amount = 0;
    $('input[name="gst_amount_array[]"]').each(function () {
        let gst_amount = parseFloat($(this).val()) || 0;
        total_gst_amount += gst_amount;
    });

    $("#master_quantity").val(totalQty);
    $("#master_amount").val(totalamount.toFixed(2));
    $("#master_taxable_amount").val(total_taxable_amount.toFixed(2));
    $('#total_taxable').val(total_taxable_amount.toFixed(2));
    $("#master_gst_amount").val(total_gst_amount.toFixed(2));
    let subtotal = parseFloat(total_gst_amount) + parseFloat(total_taxable_amount);
    $("#master_sub_total").val(subtotal.toFixed(2));
    //$("#master_sub_total").val(parseFloat(total_gst_amount.toFixed(2)) + parseFloat(total_taxable_amount.toFixed(2)));

    total_cash_discount_amount = parseFloat(total_cash_discount_amount) || 0;
    $("#cash_discount_amount").val(total_cash_discount_amount.toFixed(2));

    total_additional_discount_amount = parseFloat(total_additional_discount_amount) || 0;
    $("#additional_discount_amount").val(total_additional_discount_amount.toFixed(2));

    if ($("#is_gst_applicable").is(":checked")) {
        $('#gst_amount').val(total_gst_amount.toFixed(2));
    } else {
        $('#gst_amount').val(0.00);
    }

    if ($("#is_tcs_applicable").is(":checked")) {
        var temp_total = parseFloat($('#gst_amount').val()) + total_taxable_amount;
        var tcs_amount = temp_total * (0.1 / 100);
        $('#tcs_amount').val(tcs_amount.toFixed(2));
    } else {
        $('#tcs_amount').val(0.00);
    }

    var grand_total = parseFloat($('#gst_amount').val()) + total_taxable_amount + parseFloat($('#tcs_amount').val());
    let decimalPart = grand_total % 1;
    $('#round_off').val(decimalPart.toFixed(2));
    $('#grand_total').val(Math.round(grand_total.toFixed(2)));
}

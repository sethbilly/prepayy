@push('additional_styles')
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-slider/9.9.0/css/bootstrap-slider.min.css">
<style>
    .slider {
        margin-bottom: 13px;
    }

    .slider-horizontal {
        width: 100% !important;
    }

    .slider .slider-selection {
        background: darkred;
    }

    .tick-slider-selection {
        background: darkred !important;
    }

    .slider-tick.in-selection {
        background: lightblue !important;
    }

    .loan-product-filter-form {
        color: #fff;
    }
</style>
@endpush
@push('additional_scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-slider/9.9.0/bootstrap-slider.min.js"></script>
<script type="text/javascript">
    (function ($) {
        var selector = '.slider';
        var timeout;

        $(document).ready(function () {
            $(selector).slider();
            $(selector).show();

            $(selector).keyup(updateSliderDisplayValue);
        });

        function updateSliderDisplayValue() {
            var self = this;

            if (timeout) {
                clearTimeout(timeout);
            }

            timeout = setTimeout(function () {
                var elementValue = parseFloat($(self).val());
                var minValue = parseFloat($(self).data('slider-min'));
                var maxValue = parseFloat($(self).data('slider-max'));

                if (isNaN(elementValue) || elementValue < minValue) {
                    elementValue = minValue;
                } else if (elementValue > maxValue) {
                    elementValue = maxValue;
                }
                $(self).slider('setValue', elementValue, true, true);
            }, 500);
        }
    })(window.jQuery);
</script>
@endpush
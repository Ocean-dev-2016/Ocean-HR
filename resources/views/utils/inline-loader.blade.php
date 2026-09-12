{{-- Inline Loader Utility --}}
<style>
    .inline-loader {
        display: none;
        position: relative;
        min-height: 40px;
    }
    
    .inline-loader.active {
        display: block;
    }
    
    .inline-loader-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 100;
        border-radius: 4px;
    }
    
    .inline-loader-spinner {
        width: 30px;
        height: 30px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #007bff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    /* DataTables inline loader */
    .dataTables_wrapper .dataTables_processing {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 10px 20px;
        z-index: 100;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .dataTables_wrapper .dataTables_processing::before {
        content: '';
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid #f3f3f3;
        border-top: 2px solid #007bff;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
        margin-right: 8px;
        vertical-align: middle;
    }
</style>

<script>
    // Inline Loader Utility Functions
    window.InlineLoader = {
        // Show inline loader on an element
        show: function(selector) {
            var $target = $(selector);
            if ($target.length === 0) return;
            
            // Create loader if it doesn't exist
            if ($target.find('.inline-loader-overlay').length === 0) {
                $target.css('position', 'relative');
                $target.append('<div class="inline-loader-overlay"><div class="inline-loader-spinner"></div></div>');
            }
            $target.find('.inline-loader-overlay').show();
        },
        
        // Hide inline loader from an element
        hide: function(selector) {
            $(selector).find('.inline-loader-overlay').hide();
        },
        
        // Show loader on button and disable it
        showButton: function(selector, originalText) {
            var $btn = $(selector);
            if ($btn.length === 0) return;
            
            $btn.data('original-text', originalText || $btn.html());
            $btn.prop('disabled', true);
            $btn.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Loading...');
        },
        
        // Hide loader from button and restore text
        hideButton: function(selector) {
            var $btn = $(selector);
            if ($btn.length === 0) return;
            
            var originalText = $btn.data('original-text') || 'Submit';
            $btn.prop('disabled', false);
            $btn.html(originalText);
        }
    };
    
    // Global AJAX setup with inline loaders
    $(document).ajaxSend(function(event, xhr, settings) {
        // Don't show loader for DataTables (handled separately)
        if (settings.url && settings.url.includes('yajra-datatables') || settings.dataType === 'json' && settings.url.includes('index')) {
            return;
        }
        
        // Find the closest container or button that triggered the request
        var $trigger = $(event.target);
        if ($trigger.is('button, a, input[type="submit"]')) {
            InlineLoader.showButton($trigger);
        } else {
            // Try to find a parent container
            var $container = $trigger.closest('.card, .table-responsive, .form-container, .content-wrapper');
            if ($container.length > 0) {
                InlineLoader.show($container);
            }
        }
    });
    
    $(document).ajaxComplete(function(event, xhr, settings) {
        // Don't hide loader for DataTables
        if (settings.url && settings.url.includes('yajra-datatables') || settings.dataType === 'json' && settings.url.includes('index')) {
            return;
        }
        
        var $trigger = $(event.target);
        if ($trigger.is('button, a, input[type="submit"]')) {
            InlineLoader.hideButton($trigger);
        } else {
            var $container = $trigger.closest('.card, .table-responsive, .form-container, .content-wrapper');
            if ($container.length > 0) {
                InlineLoader.hide($container);
            }
        }
    });
    
    // Global DataTables configuration with inline loader
    if ($.fn.dataTable) {
        $.extend(true, $.fn.dataTable.defaults, {
            processing: true,
            language: {
                processing: '<div class="spinner-border spinner-border-sm me-2" role="status"></div> Processing...'
            },
            drawCallback: function(settings) {
                // Ensure loader is hidden after draw
                $(this.api().table().container()).find('.dataTables_processing').hide();
            }
        });
        
        // Show inline loader for DataTables
        $(document).on('preXhr.dt', function(e, settings, data) {
            var $table = $(settings.nTable);
            var $wrapper = $table.closest('.dataTables_wrapper');
            if ($wrapper.length > 0) {
                InlineLoader.show($wrapper);
            }
        });
        
        // Hide inline loader for DataTables
        $(document).on('xhr.dt', function(e, settings, json, xhr) {
            var $table = $(settings.nTable);
            var $wrapper = $table.closest('.dataTables_wrapper');
            if ($wrapper.length > 0) {
                InlineLoader.hide($wrapper);
            }
        });
    }
</script>

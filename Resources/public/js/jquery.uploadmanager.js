(function( $ ) {
    $.fn.uploadmanager = function() {
        this.each(function() {
            // Field options
            var o = {
                unique_id: $(this).val(),
                upload_url: $(this).attr('data-upload-url'),
                action_url: '/KoernerWS/UploadManagerBundle/web/app_dev.php/_upload/'
            };

            // Methods
            var m = {
                parseTemplate: function (template, filename, split) {
                    var tpl = $(template);
                    
                    if (typeof split !== undefined && split === false) {
                        var file = filename;
                    } else {
                        var file = filename.split('-').slice(1).join('-');
                    }
                    
                    tpl.attr('data-upload-file', filename);
                    $('.file-name', tpl).text(file);
                    
                    return tpl;
                }
            };
            
            // Selector shortcut
            var dui_sel = '[data-upload-id="' + o.unique_id + '"]';
            
            // Templates
            var t = {
                existing_item: $('script' + dui_sel + '[data-upload-template="existing-item"]').html(),
                added_item: $('script' + dui_sel + '[data-upload-template="added-item"]').html(),
                process_item: $('script' + dui_sel + '[data-upload-template="process-item"]').html(),
                removed_item: $('script' + dui_sel + '[data-upload-template="removed-item"]').html()
            };
            
            // Changing upload field
            $('input[type="file"]' + dui_sel).bind('change', function() {
                if (!this.files){
                    return false; // Empty upload field
                }
                
                $.each(this.files, function(){
                    // Create request object
                    var request = new XMLHttpRequest();
                    request.open('POST', o.upload_url, true);
                    
                    // Create template
                    var tpl = m.parseTemplate(t.process_item, this.name, false);
                    
                    // Write template
                    $('ul' + dui_sel + '[data-upload-list="added"]').append(tpl);
                    
                    // Collect data
                    var data = new FormData();
                    data.append('unique_id', o.unique_id);
                    data.append('file', this);
                    
                    $.ajax({
                        type: 'POST',
                        url: o.upload_url,
                        data: data,
                        processData: false,
                        contentType: false,
                        xhr: function() {
                            var xhr = jQuery.ajaxSettings.xhr();

                            if (xhr instanceof window.XMLHttpRequest) {
                                var extended = $('.extended span', tpl);
                                
                                xhr.upload.addEventListener('progress', function(evt){
                                    if (evt.lengthComputable) {
                                        extended.text((evt.loaded / evt.total * 100).toFixed(2) + '%');
                                    }
                                });
                            }
                            return xhr;
                        },
                        success: function(filename){
                            var new_tpl = m.parseTemplate(t.added_item, filename);
                            tpl.replaceWith(new_tpl);
                        },
                        error: function(xhr, ajaxOptions, thrownError){
                            alert(thrownError);
                            tpl.remove();
                        }
                    });
                });
            });
            
            // Existing files
            $('ul' + dui_sel + '[data-upload-list="existing"]').on('click', 'a[href="#delete"]', function(){
                var parent = $(this).closest('[data-upload-file]');
                
                $.ajax({
                    type: 'POST',
                    url: o.action_url,
                    data: {
                        unique_id: o.unique_id,
                        action: 'delete_existing',
                        file: parent.attr('data-upload-file')
                    },
                    success: function(data) {
                        // Copy attribute and file name
                        var tpl = m.parseTemplate(t.removed_item, parent.attr('data-upload-file'));

                        // Remove old item
                        parent.remove();

                        // Add new item
                        $('ul' + dui_sel + '[data-upload-list="removed"]').append(tpl);
                    },
                    error: function() {
                        
                    }
                });
            });
            
            // Added files
            $('ul' + dui_sel + '[data-upload-list="added"]').on('click', 'a[href="#delete"]', function(){
                var parent = $(this).closest('[data-upload-file]');
                
                $.ajax({
                    type: 'POST',
                    url: o.action_url,
                    data: {
                        unique_id: o.unique_id,
                        action: 'delete_added',
                        file: parent.attr('data-upload-file')
                    },
                    success: function(data) {
                        parent.remove();
                    },
                    error: function() {
                        
                    }
                });
            });
            
            // Removed files
            $('ul' + dui_sel + '[data-upload-list="removed"]').on('click', 'a[href="#restore"]', function(){
                var parent = $(this).closest('[data-upload-file]');
                
                $.ajax({
                    type: 'POST',
                    url: o.action_url,
                    data: {
                        unique_id: o.unique_id,
                        action: 'restore_deleted',
                        file: parent.attr('data-upload-file')
                    },
                    success: function(data) {
                        // Copy attribute and file name
                        var tpl = m.parseTemplate(t.existing_item, parent.attr('data-upload-file'));

                        // Remove old item
                        parent.remove();

                        // Add new item
                        $('ul' + dui_sel + '[data-upload-list="existing"]').append(tpl);
                    },
                    error: function() {
                        
                    }
                });
            });
        });
    };
})( jQuery );
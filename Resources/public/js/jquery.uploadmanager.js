/*
 * (c) Florian Koerner <f.koerner@checkdomain.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

(function( $ ) {
    $.fn.uploadmanager = function() {
        this.each(function() {
            var $this = $(this);
            
            // Field options
            var o = {
                unique_id: $this.attr('data-upload-id'),
                upload_url: $this.attr('data-upload-url'),
                action_url: $this.attr('data-upload-actions')
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
            
            // Templates
            var t = {
                existing_item: $('script[data-upload-template="existing-item"]', $this).html(),
                added_item: $('script[data-upload-template="added-item"]', $this).html(),
                process_item: $('script[data-upload-template="process-item"]', $this).html(),
                removed_item: $('script[data-upload-template="removed-item"]', $this).html()
            };
            
            // Click on upload icon button
            $('.btn-upload', $this).click(function(){
                $('input[type="file"]', $this).click();
            });
            
            // Upload function
            var ajax_upload = function (files) {
                $.each(files, function(){
                    // Create request object
                    var request = new XMLHttpRequest();
                    request.open('POST', o.upload_url, true);
                    
                    // Create template
                    var tpl = m.parseTemplate(t.process_item, this.name, false);
                    
                    // Write template
                    $('ul[data-upload-list="added"]', $this).append(tpl);
                    
                    // Collect data
                    var data = new FormData();
                    data.append('unique_id', o.unique_id);
                    data.append('file', this);
                    
                    $.ajax({
                        type: 'POST',
                        dataType: "json",
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
                        success: function(response){
                            if (jQuery.type(response.errors) !== "undefined") {
                                alert('- ' + response.errors.join("\n- "));
                                tpl.remove();
                            } else {
                                var new_tpl = m.parseTemplate(t.added_item, response.data);
                                tpl.replaceWith(new_tpl);
                            }
                        },
                        error: function(xhr, ajaxOptions, thrownError){
                            alert(thrownError);
                            tpl.remove();
                        }
                    });
                });
            };
            
            // Drag and drop
            $this.bind('dragover', function(evt) {
                evt.stopPropagation();
                evt.preventDefault();
                
                evt.originalEvent.dataTransfer.dropEffect = 'copy';
            });
            
            $this.bind('drop', function(evt, test) {
                evt.stopPropagation();
                evt.preventDefault();

                ajax_upload(evt.originalEvent.dataTransfer.files);
            });
            
            // Changing upload field
            $('input[type="file"]', $this).bind('change', function(evt) {
                if (!this.files){
                    return false; // Empty upload field
                }
                
                ajax_upload(this.files);
                
                $(this).replaceWith($(this).val('').clone(true));
                
                evt.stopPropagation();
                evt.preventDefault();
            });
            
            // Existing files
            $('ul[data-upload-list="existing"]', $this).on('click', 'a[href="#delete"]', function(evt){
                var parent = $(this).closest('[data-upload-file]');
                
                $.ajax({
                    type: 'POST',
                    url: o.action_url,
                    data: {
                        unique_id: o.unique_id,
                        action: 'delete_existing',
                        file: parent.attr('data-upload-file')
                    },
                    success: function(response) {
                        if (jQuery.type(response.errors) !== "undefined") {
                            alert('- ' + response.errors.join("\n- "));
                            tpl.remove();
                        } else {
                            // Copy attribute and file name
                            var tpl = m.parseTemplate(t.removed_item, parent.attr('data-upload-file'));

                            // Remove old item
                            parent.remove();

                            // Add new item
                            $('ul[data-upload-list="removed"]', $this).append(tpl);
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        alert(thrownError);
                    }
                });
                
                evt.stopPropagation();
                evt.preventDefault();
            });
            
            // Added files
            $('ul[data-upload-list="added"]', $this).on('click', 'a[href="#delete"]', function(evt){
                var parent = $(this).closest('[data-upload-file]');
                
                $.ajax({
                    type: 'POST',
                    url: o.action_url,
                    data: {
                        unique_id: o.unique_id,
                        action: 'delete_added',
                        file: parent.attr('data-upload-file')
                    },
                    success: function(response) {
                        if (jQuery.type(response.errors) !== "undefined") {
                            alert('- ' + response.errors.join("\n- "));
                            tpl.remove();
                        } else {
                            parent.remove();
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        alert(thrownError);
                    }
                });
                
                evt.stopPropagation();
                evt.preventDefault();
            });
            
            // Removed files
            $('ul[data-upload-list="removed"]', $this).on('click', 'a[href="#restore"]', function(evt){
                var parent = $(this).closest('[data-upload-file]');
                
                $.ajax({
                    type: 'POST',
                    url: o.action_url,
                    data: {
                        unique_id: o.unique_id,
                        action: 'restore_deleted',
                        file: parent.attr('data-upload-file')
                    },
                    success: function(response) {
                        if (jQuery.type(response.errors) !== "undefined") {
                            alert('- ' + response.errors.join("\n- "));
                            tpl.remove();
                        } else {
                            // Copy attribute and file name
                            var tpl = m.parseTemplate(t.existing_item, parent.attr('data-upload-file'));

                            // Remove old item
                            parent.remove();

                            // Add new item
                            $('ul[data-upload-list="existing"]', $this).append(tpl);
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        alert(thrownError);
                    }
                });
                
                evt.stopPropagation();
                evt.preventDefault();
            });
        });
    };
})( jQuery );
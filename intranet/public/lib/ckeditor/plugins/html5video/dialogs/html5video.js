CKEDITOR.dialog.add( 'html5video', function( editor ) {
    return {
        title: editor.lang.html5video.title,
        minWidth: 500,
        minHeight: 100,
        contents: [
            {
                id: 'info',
                label: editor.lang.html5video.infoLabel,
                elements: [
                    {
                        type: 'file',
                        id: 'audio_upload',
                        label: 'Upload audio ' + '('+ editor.lang.html5video.allowed +')',
                        onClick: function () {
                            this.setValue('');
                            var input = this.getInputElement();
                            input.$.accept = 'audio/*';
                        },
                        onChange: function () {
                            var dialog = this.getDialog();
                            var audioUrlElm = dialog.getContentElement('info', 'url');
                            var files = this.getInputElement().$.files;
                            if (files.length < 1) {
                                return;
                            }
                            var formData = new FormData();
                            for (var i = 0; i < files.length; i++) {
                                formData.append('images[]', files[i]);
                            }
                            formData.append('_token', editor.config.extraConfig._token);
                            formData.append('return_url', 1);
                            formData.append('mimetype', 'audio');

                            $.ajax({
                                url: editor.config.extraConfig.urlUploadImage,
                                type: 'POST',
                                data: formData,
                                processData: false,
                                contentType: false,
                                success: function (result) {
                                    if (result) {
                                        audioUrlElm.setValue(result[0]);
                                    }
                                },
                                error: function (error){
                                    var mess = error.responseJSON || 'Không tải được ảnh lên, vui lòng thử lại!';
                                    alert(mess);
                                }
                            });
                        }
                    },
                    {
                        type: 'text',
                        id: 'url',
                        label: editor.lang.html5video.orInputUrl,
                        required: true,
                        validate: CKEDITOR.dialog.validate.notEmpty( editor.lang.html5video.urlMissing ),
                        setup: function( widget ) {
                            this.setValue( widget.data.src );
                        },
                        commit: function( widget ) {
                            widget.setData( 'src', this.getValue() );
                        }
                    },
                    {
                        type: 'checkbox',
                        id: 'responsive',
                        label: editor.lang.html5video.responsive,
                        setup: function( widget ) {
                            this.setValue( widget.data.responsive );
                        },
                        commit: function( widget ) {
                            widget.setData( 'responsive', this.getValue()?'true':'' );
                        }
                    },
                    {
                        type: 'hbox',
                        id: 'alignment',
                        children: [ {
                            type: 'radio',
                            id: 'align',
                            label: editor.lang.common.align,
                            items: [
                                [editor.lang.common.alignCenter, 'center'],
                                [editor.lang.common.alignLeft, 'left'],
                                [editor.lang.common.alignRight, 'right'],
                                [editor.lang.common.alignNone, 'none']
                            ],
                            'default': 'center',
                            setup: function( widget ) {
                                if ( widget.data.align ) {
                                    this.setValue( widget.data.align );
                                }
                            },
                            commit: function( widget ) {
                                widget.setData( 'align', this.getValue() );
                            }
                        } ]
                    },
                    {
                        type: 'radio',
                        id: 'autoplay',
                        label: editor.lang.html5video.autoplay,
                        items: [
                            [editor.lang.html5video.yes, 'yes'],
                            [editor.lang.html5video.no, 'no']
                        ],
                        'default': 'no',
                        setup: function( widget ) {
                            if ( widget.data.autoplay ) {
                                this.setValue( widget.data.autoplay );
                            }
                        },
                        commit: function( widget ) {
                            widget.setData( 'autoplay', this.getValue() );
                        }
                    }
                ]
            },
            {
                id: 'Upload',
                hidden: true,
                filebrowser: 'uploadButton',
                label: editor.lang.html5video.upload,
                elements: [ {
                    type: 'file',
                    id: 'upload',
                    label: editor.lang.html5video.btnUpload,
                    style: 'height:40px',
                    size: 38
                },
                {
                    type: 'fileButton',
                    id: 'uploadButton',
                    filebrowser: 'info:url',
                    label: editor.lang.html5video.btnUpload,
                    'for': [ 'Upload', 'upload' ]
                } ]
            }
        ]
    };
} );

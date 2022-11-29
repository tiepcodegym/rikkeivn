CKEDITOR.dialog.add('rkimgDialog', function (editor) {
   return {
       title: 'Chèn ảnh',
       minWidth: 400,
       minHeight: 200,
       contents: [
           {
                id: 'tab-upload',
                label: 'Basic Settings',
                elements: [
                    {
                        type: 'file',
                        id: 'image_upload',
                        label: 'Tải lên hình ảnh (cho phép các file png, jpeg, jpg, gif)',
                        onClick: function () {
                            this.setValue('');
                            var input = this.getInputElement();
                            input.$.accept = 'image/*';
                        },
                        onChange: function () {
                            var dialog = this.getDialog();
                            var imageUrlElm = dialog.getContentElement('tab-upload', 'image_url');
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
                            formData.append('mimetype', 'image');
                            
                            $.ajax({
                                url: editor.config.extraConfig.urlUploadImage,
                                type: 'POST',
                                data: formData,
                                processData: false,
                                contentType: false,
                                success: function (result) {
                                    if (result) {
                                        imageUrlElm.setValue(result[0]);
                                    }
                                },
                                error: function (error){
                                    alert(error.responseJSON || 'Không tải được ảnh lên, vui lòng thử lại!');
                                }
                            });
                        }
                    },
                    {
                        type: 'text',
                        id: 'image_url',
                        label: 'Hoặc nhập đường dẫn ảnh',
                        validate: CKEDITOR.dialog.validate.notEmpty( "Đường dẫn ảnh không được trống" )
                    }
                ]
            }
       ],
       onOk: function () {
            var dialog = this;
            var imageUrl = dialog.getValueOf('tab-upload', 'image_url');
            editor.insertHtml('<img src="'+ imageUrl +'">');
       }
   }; 
});



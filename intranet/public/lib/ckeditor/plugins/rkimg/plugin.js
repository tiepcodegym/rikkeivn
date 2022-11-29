CKEDITOR.plugins.add('rkimg', {
    icons: 'rkimg',
    init: function (editor) {
        editor.addCommand('RkimageDialog', new CKEDITOR.dialogCommand( 'rkimgDialog' ));
        editor.ui.addButton('Rkimage', {
           label: 'Chèn hình ảnh',
           command: 'RkimageDialog',
           toolbar: 'insert',
           icon: this.path + 'icons/image.png'
        });
        CKEDITOR.dialog.add( 'rkimgDialog', this.path + 'dialogs/rkimg.js' );
    }
});



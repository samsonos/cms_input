/**
 * Created by Maxim Omelchenko on 23.02.2015 at 12:29.
 */

var SamsonCMS_BindInput = function(block){
    SamsonCMS_InputField(s('.__inputfield.__textarea', block));
    SamsonCMS_InputUpload(s('.__fieldUpload', block));
    SamsonCMS_InputSelect(s('.__inputfield.__select', block));
    SamsonCMS_InputNumber(s('.__inputfield.__number', block));
    SamsonCMS_InputMaterial(s('.field_material_btn_select', block));
};
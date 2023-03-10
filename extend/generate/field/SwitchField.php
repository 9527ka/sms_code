<?php
/**
 * @author yupoxiong<i@yufuping.com>
 */

namespace generate\field;

class SwitchField extends Field
{
    public static string $html = <<<EOF
<div class="form-group row rowSwitch">
    <label for="[FIELD_NAME]" class="col-sm-2 col-form-label">[FORM_NAME]</label>
    <div class="col-sm-10 col-md-4 formInputDiv">
    <input {if((!isset(\$data)&&[FIELD_DEFAULT]==1)||(isset(\$data)&&\$data.[FIELD_NAME]==1))}checked{/if}
     class="input-switch"  id="[FIELD_NAME]" value="1" type="checkbox" />
    <input class="switch fieldSwitch" placeholder="[FORM_NAME]" name="[FIELD_NAME]" value="{\$data.[FIELD_NAME]|default='[FIELD_DEFAULT]'}" hidden />
    </div>

    <script>
        $('#[FIELD_NAME]').bootstrapSwitch({
            onText: "[ON_TEXT]",
            offText: "[OFF_TEXT]",
            onColor: "success",
            offColor: "danger",
            onSwitchChange: function (event, state) {
                $(event.target).closest('.bootstrap-switch').next().val(state ? '1' : '0').change();
            }
        });
    </script>
</div>\n
EOF;


    public static function create($data): string
    {
        $html = self::$html;
        //switch开的文字
        if (isset($data['on_text'])) {
            $html = str_replace('[ON_TEXT]', $data['on_text'], $html);
        } else {
            $html = str_replace('[ON_TEXT]', '是', $html);
        }
        //switch关的文字
        if (isset($data['off_text'])) {
            $html = str_replace('[OFF_TEXT]', $data['off_text'], $html);
        } else {
            $html = str_replace('[OFF_TEXT]', '否', $html);
        }

        return str_replace(array('[FORM_NAME]', '[FIELD_NAME]', '[FIELD_DEFAULT]'), array($data['form_name'], $data['field_name'], $data['field_default']), $html);
    }
}
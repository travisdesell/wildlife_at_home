<?php

class Form {
    private $inputs = array();
    private $prefix;
    private $title;
    private $description;
    private $target;

    public function __construct(string $prefix, string $target, string $title, string $description) {
        $this->prefix = $prefix;
        $this->target = $target;
        $this->title = $title;
        $this->description = $description;
    }

    public function append(FormInput $input) {
        $this->inputs[] = $input;
    }

    public function html() : string {
        $inputs = "";
        foreach ($this->inputs as &$input) {
            $inputs .= $input->html($this->prefix);
        }

        return "
<div class='row'>
    <div class='col-md-4'>
        <h3>" . $this->title ."</h3>
        <p>" . $this->description . "</p>
    </div>

    <div class='col-md-8'>
        <p class='hidden' id='" . $this->prefix . "_form_processing'>Processing... This may take a while. Downloading will automatically begin when ready.</p>

        <form class='form-horizontal' id='" . $this->prefix ."_form'>
            $inputs

            <div class='form-group'>
                <div class='col-sm-offset-2 col-sm-10'>
                    <button type='submit' class='btn btn-primary btn-lg' id='" . $this->prefix ."_form_submit'>Generate</button>
                </div>
            </div>
        </form>
        </form>
    </div>
</div>";
    }

    public function js_onload() : string {
        $js = "";
        foreach ($this->inputs as &$input) {
            $js .= $input->js_onload($this->prefix);
        }

        return $js;
    }

    public function js() : string {
        $js = "";
        foreach ($this->inputs as &$input) {
            $js .= $input->js($this->prefix);
        }

        $id = $this->prefix . "_form";

        return "
$js;

$('#$id').submit(function() {
    var form = $('#$id');
    var form_processing = $('#${id}_processing');

    hide_form(form, form_processing);

    $.post('" . $this->target . "', form.serialize())
        .done(function(data) {
            data = $.parseJSON(data);

            if (data.status == 'error') {
                error_message(data.error, form, form_processing);
                return;
            }

            console.log(data);

            check_status(data, form, form_processing);
        })
        .fail(function() {
            error_message('Error 500', form, form_processing);
        });

    event.preventDefault();
});";
    }
}

abstract class FormInput {
    protected $name;
    protected $label;

    protected function __construct(string $name, string $label) {
        $this->name = $name;
        $this->label = $label;
    }

    abstract public function html(string $prefix) : string;

    public function js_onload(string $prefix) : string {
        return "";
    }

    public function js(string $prefix) : string {
        return "";
    }

    protected function id(string $prefix) : string {
        return "$prefix" . "_" . $this->name;
    }

    protected function start_div(string $prefix) : string {
        return "
<div class='form-group'>
    <label for='" . $this->id($prefix) . "' class='col-sm-2 control-label'>" . $this->label . "</label>
    <div class='col-sm-10 input-group'>";
    }

    protected function end_div() : string {
        return "
    </div>
</div>";
    }
}

class FormInputText extends FormInput {
    protected $val, $addon;

    public function __construct(string $name, string $label, string $val = "", string $addon = "") {
        parent::__construct($name, $label);

        $this->val = $val;
        $this->addon = $addon;
    }

    public function setVal(string $val) {
        $this->val = $val;
    }

    public function setAddon(string $addon) {
        $this->addon = $addon;
    }

    public function html(string $prefix) : string {
        $params = array();
        $addon = "";

        if ($this->val)
            $params[] = "value='" . $this->val . "'";

        if ($this->addon)
            $addon = "<span class='input-group-addon'>" . $this->addon . "</span>";

        return 
$this->start_div($prefix) . "
    <input type='number' class='form-control' name='" . $this->name . "' ".implode(" ", $params)." id='" . $this->id($prefix) . "'>
        $addon" .
$this->end_div($prefix);
    }
}

class FormInputNumber extends FormInputText {
    private $min, $max;

    public function __construct(string $name, string $label, int $min = 0, int $max = 0, int $val = 0, string $addon = "") {
        parent::__construct($name, $label, $val ? "$val" : "", $addon);

        $this->min = $min;
        $this->max = $max;
    }

    public function setRange(int $min, int $max) {
        $this->min = $min;
        $this->max = $max;
    }

    public function html(string $prefix) : string {
        $params = array();
        $addon = "";

        if ($this->min)
            $params[] = "min='" . $this->min . "'";
        if ($this->max)
            $params[] = "max='" . $this->max . "'";
        if ($this->val)
            $params[] = "value='" . $this->val . "'";

        if ($this->addon)
            $addon = "<span class='input-group-addon'>" . $this->addon . "</span>";

        return 
$this->start_div($prefix) . "
    <input type='number' class='form-control' name='" . $this->name . "' ".implode(" ", $params)." id='" . $this->id($prefix) . "'>
        $addon" .
$this->end_div($prefix);
    }
}

class FormInputCheckbox extends FormInput {
    private $checked;

    public function __construct(string $name, string $label, bool $checked = false) {
        parent::__construct($name, $label);

        $this->checked = $checked;
    }
    public function html(string $prefix) : string {
        $checked = $this->checked ? "checked" : "";

        return 
$this->start_div($prefix) . "
    <input type='checkbox' id='" . $this->id($prefix) . "' name='" . $this->name . "' $checked>" .
$this->end_div($prefix);
    }
}

class FormInputDropdown extends FormInput {
    private $options = array();
    private $selected = "";

    public function __construct(string $name, string $label) {
        parent::__construct($name, $label);
    }

    public function append(string $val, string $text, bool $selected = false) {
        if ((!$val && $val !== "0") || (!$text && $text !== "0")) {
            return;
        }

        $this->options[$val] = $text;
        if ($selected) {
            $this->selected = $val;
        }
    }

    public function html(string $prefix) : string {
        $options = "";

        // add in each option
        foreach ($this->options as $val => &$option) {
            $selected = ($val === $this->selected) ? "selected" : "";
            $options .= "<option value='$val' $selected>$option</option>";
        }

        return 
$this->start_div($prefix) . "
    <select class='form-control' name='" . $this->name . "' id='" . $this->id($prefix) . "'>
        $options
    </select>" .
$this->end_div($prefix);
    }
}

class FormInputDatepair extends FormInput {
    public function __construct(string $name, string $label) {
        parent::__construct($name, $label);
    }

    public function html(string $prefix) : string {
    }

    public function js_onload(string $prefix) : string {
    }

    public function js(string $prefix) : string {
    }
} 

?>

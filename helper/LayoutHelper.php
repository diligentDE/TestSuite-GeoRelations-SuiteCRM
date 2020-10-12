<?php
class LayoutHelper {
    public static function insertCss(){
        echo <<<html
<head>
<style>
.success {
    background-color: darkseagreen;
}
.failed {
    background-color: crimson;
}
.resultrow {
    font-weight: bold;
    background-color: darkslategrey;
    color: ghostwhite;
}
table > thead > tr {
    background-color: darkgrey;
}
table {
    margin-bottom: 5em;
}

</style>
</head>
html;
    }
}
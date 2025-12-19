<?php

function csvToAssoc($file)
{
    $rows = array_map('str_getcsv', file($file, FILE_SKIP_EMPTY_LINES));
    $headers = array_shift($rows);

    return array_map(function ($row) use ($headers) {
        $row = array_pad($row, count($headers), null);
        $row = array_slice($row, 0, count($headers));

        return array_combine($headers, $row);
    }, $rows);
}

// read base colours
$csv = csvToAssoc("../line-colors.csv");

// read Swiss colours
$csv_CH = csvToAssoc("../line-colors-CH.csv");

// merge both CSVs
$csv = array_merge($csv, $csv_CH);


$linesByOperatorCode = array_reduce($csv, function ($result, $line) {
    $result[$line["shortOperatorName"]][] = $line;

    return $result;
}, []);


function valid_shape($line, $i) {
    if (!in_array($line["shape"], ["circle", "hexagon", "pill", "rectangle", "rectangle-rounded-corner", "trapezoid"])) {
        throw new Error("bad shape " . $line["shape"] . " in row $i");
    }
}

function valid_hex_color($line, $i, $key) {
    $color = $line[$key];

    if (!(strlen($color) == 7 && ctype_xdigit(substr($color, 1)) && $color[0] === "#")) {
        throw new Error("bad $key \"$color\" does not follow #<6 digit hex color> in row $i");
    }
}

function text_color_differs_background($line, $i) {
    $textColor = $line["textColor"];
    $backgroundColor = $line["backgroundColor"];
    if ($textColor === $backgroundColor) {
        throw new Error("bad color combination: text color \"$textColor\" may not be background color \"$backgroundColor\" in row $i");
    }
}

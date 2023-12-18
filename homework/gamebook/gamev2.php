<?php
function renderHeader($title){
    echo "    <!DOCTYPE HTML>";
    echo "<html lang=\"en\">";
    echo "<head>";
    echo "  <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">";
    echo "  <title>" . htmlspecialchars($title) . "</title>";
    echo "  <style>";
    echo "    .site {";
    echo "      border: 2px solid gray; padding: 1rem;";
    echo "    }";
    echo "  </style>";
    echo "</head>";
}


function renderBasicSite($site, $statistics) {
    echo "<div class='site'>";
    echo "<div class='content'>" . substituteText($site['text'], $statistics) . "</div>";
    if (!empty($site['actions'])) {
        echo "<ul class='actions'>";
        foreach ($site['actions'] as $action) {
            if (isActionVisible($action, $statistics)) {
                $newStatistics = applyEffects($action['effect'], $statistics);
                echo "<li><a href='?" . http_build_query(['site' => $action['site']] + buildStatisticsQuery($newStatistics)) . "'>" . substituteText($action['text'], $newStatistics) . "</a></li>";
            }
        }
        echo "</ul>";
    }
    echo "</div>";
}

function renderInputStringSite($site, $statistics) {
    echo "<div class='site'>";
    echo "<div class='content'><p>" . substituteText($site['text'], $statistics) . "</p>";
    echo "<form class='form' action='' method='get'>";
    echo "<input type='hidden' name='site' value='" . htmlspecialchars($site['site']) . "'>";
    foreach ($statistics as $key => $value) {
        echo "<input type='hidden' name='statistics." . htmlspecialchars($key) . "' value='" . htmlspecialchars($value) . "'>";
    }
    echo "<label for='inputString'>" . htmlspecialchars($site['label']) . "</label>";
    echo "<input type='text' id='inputString' name='statistics." . htmlspecialchars($site['target']) . "'>";
    echo "<input type='submit' value='Submit'>";
    echo "</form></div>";
    echo "</div>";
}

function substituteText($text, $statistics) {
    $parts = explode('{', $text);

    foreach ($parts as &$part) {
        $endPos = strpos($part, '}');
        if ($endPos !== false) {
            $key = substr($part, 0, $endPos);
            if (!isset($statistics[$key])) {
                ErrorMissingProperty($key);
            }
            $replacement = htmlspecialchars($statistics[$key]);
            $part = $replacement . substr($part, $endPos + 1);
        }
    }
    return implode('', $parts);
}

function ErrorMissingProperty($key){
    echo "Missing property \"{$key}\"";
    http_response_code(400);
    exit;
}

function isActionVisible($action, $statistics) {
    if (empty($action['visibility'])) {
        return true;
    }
    foreach ($action['visibility'] as $key => $condition) {
        if (!checkCondition($key, $condition, $statistics)) {
            return false;
        }
    }
    return true;
}

function applyEffects($effects, $statistics) {
    if (empty($effects)) {
        return $statistics;
    }
    foreach ($effects as $key => $effect) {
        $statistics[$key] = calculateEffect($key, $effect, $statistics);
    }
    return $statistics;
}

function checkCondition($key, $condition, $statistics) {
    $value = $statistics[$key] ?? 0;
    if (!is_numeric($value)) {
        echo 'Invalid variable value.';
        http_response_code(500);
        exit;
    }
    $operator = $condition[0];
    $number = substr($condition, 1);
    if (!is_numeric($number)) {
        echo 'Invalid variable value.';
        http_response_code(500);
        exit;
    }
    switch ($operator) {
        case '>': return $value > $number;
        case '<': return $value < $number;
        default:
        echo 'Missing Variable.';
        http_response_code(500);
        exit;
    }
    return false;
}

function calculateEffect($key, $effect, $statistics) {
    $currentValue = $statistics[$key] ?? 0;
    if (!is_numeric($currentValue)) {
        echo 'Invalid variable value.'; 
        http_response_code(500);
        exit;
    }
    $operator = $effect[0];
    $number = substr($effect, 1);
    if (!is_numeric($number)) {
        echo 'Invalid variable value.';
        http_response_code(500);
        exit;
    }
    switch ($operator) {
        case '=': return $number;
        case '+': return $currentValue + $number;
        case '-': return $currentValue - $number;
        default:
        echo 'Missing Variable.';
        http_response_code(500);
        exit;
    }
    return $currentValue;
}

function buildStatisticsQuery($statistics) {
    $query = [];
    foreach ($statistics as $key => $value) {
        $query['statistics.' . $key] = $value;
    }
    return $query;
}

$adventureDefinitionUrl = 'https://webik.ms.mff.cuni.cz/nswi142/php-assignment/story.json';


$adventureJson = file_get_contents($adventureDefinitionUrl);
if (!$adventureJson) {
    http_response_code(500);
    exit;
}

$adventure = json_decode($adventureJson, true);
if (!$adventure) {
    http_response_code(500);
    exit;
}

$statistics = [];
foreach ($_GET as $key => $value) {
    if (strpos($key, 'statistics_') === 0) {
        $statKey = str_replace('statistics_', '', $key);
        $statistics[$statKey] = $value;
    }
}

$site = $_GET['site'] ?? $adventure['starting-site'];

$currentSite = $adventure['sites'][$site] ?? null;
if (!$currentSite) {
    http_response_code(404);
    exit;
}

renderHeader($adventure['title']);

switch ($currentSite['type']) {
    case 'basic':
        renderBasicSite($currentSite, $statistics);
        break;
    case 'input-string':
        renderInputStringSite($currentSite, $statistics);
        break;
    default:
        http_response_code(500);
        exit;
    
}


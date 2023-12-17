<?php
// Load adventure definition from the provided URL
$definitionUrl = 'https://webik.ms.mff.cuni.cz/nswi142/php-assignment/story.json';
$definition = json_decode(file_get_contents($definitionUrl), true);

// Function to validate and get the current state
function getCurrentState() {
    // Extract site and statistics from the URL query
    $site = $_GET['site'] ?? $GLOBALS['definition']['starting-site'];
    $statistics = $_GET['statistics'] ?? [];

    // Replace underscores with periods in statistics keys
    $statistics = array_combine(
        array_map(static function ($key) {
            return str_replace('_', '.', $key);
        }, array_keys($statistics)),
        $statistics
    );

    return compact('site', 'statistics');
}

// Function to handle errors with appropriate HTTP status and message
function handleError($status, $message) {
    http_response_code($status);
    die($message);
}
// Function to validate and get a specific site definition
function getSiteDefinition($siteId) {
    $sites = $GLOBALS['definition']['sites'];

    // Check if the site with the given identifier exists
    if (!isset($sites[$siteId])) {
        handleError(404, 'Site not found.');
    }

    return $sites[$siteId];
}

// Function to validate and apply effects to player statistics

// Adjusted applyEffects function
function applyEffects($statistics, $effects) {
    foreach ($effects as $statistic => $effect) {
        $operation = $effect[0];
        $value = substr($effect, 1);

        if (!isset($statistics[$statistic])) {
            // Initialize statistic if not set
            $statistics[$statistic] = ($operation === '+') ? 0 : 0;
        }

        switch ($operation) {
            case '=':
                $statistics[$statistic] = (is_numeric($value)) ? (int)$value : $value;
                break;
            case '+':
                $statistics[$statistic] += (int)$value;
                break;
            case '-':
                $statistics[$statistic] -= (int)$value;
                break;
            default:
                handleError(500, 'Invalid variable operation.');
        }
    }

    return $statistics;
}

function renderSite($site, $currentState) {
    echo '<div class="site">';
    echo '<p>' . substituteText($site['text'], $currentState['statistics']) . '</p>';

    if ($site['type'] === 'input-string') {
        $target = isset($site['target']) ? $site['target'] : '';

        if ($target === '') {
            // Handle error if the 'target' property is missing
            handleError(400, 'Missing target property for input-string site.');
        }

        $siteParam = 'site=' . $site['site'];
        $statisticsParams = http_build_query($currentState['statistics'], '', '&', PHP_QUERY_RFC3986);
        $formAction = '?' . $siteParam;

        if ($statisticsParams !== '') {
            $formAction .= '&' . $statisticsParams;
        }

        echo '<form method="get" action="' . $formAction . '">';
        echo '<label for="' . $target . '">' . $site['label'] . '</label>';
        echo '<input type="text" name="statistics[' . str_replace('.', '_', $target) . ']" id="' . $target . '" value="' . htmlspecialchars($currentState['statistics'][$target] ?? '') . '" required>';
        echo '<input type="submit" value="Submit">';
        echo '</form>';
    } else {
        if (isset($site['actions'])) {
            echo '<ul class="actions">';
            foreach ($site['actions'] as $action) {
                $actionText = isset($action['text']) ? substituteText($action['text'], $currentState['statistics']) : '';
                $actionSite = isset($action['site']) ? $action['site'] : '';
                $actionTarget = isset($action['target']) ? $action['target'] : '';

                if (!isset($action['visibility']) || checkVisibility($currentState['statistics'], $action['visibility'])) {
                    // Include existing statistics in the URL query parameters
                    $actionParams = http_build_query(['site' => $actionSite] + $currentState['statistics'], '', '&', PHP_QUERY_RFC3986);
                    echo '<li><a href="?' . $actionParams . '">' . $actionText . '</a></li>';
                }
            }
            echo '</ul>';
        }
    }

    echo '</div>';
}


// Adjusted substituteText function
function substituteText($text, $statistics) {
    preg_match_all('/\{(.*?)\}/', $text, $matches);

    foreach ($matches[1] as $match) {
        $property = str_replace('.', '_', $match);
        if (isset($statistics[$property])) {
            $text = str_replace('{' . $match . '}', $statistics[$property], $text);
        } else {
            // Replace with a placeholder or an empty string if the property is not set
            $text = str_replace('{' . $match . '}', '[unknown]', $text);
        }
    }

    return $text;
}

$currentState = getCurrentState();
$currentSite = getSiteDefinition($currentState['site']);

// Adjusted form submission handling
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($currentSite['type']) && $currentSite['type'] === 'input-string') {
    // Process the form submission and update player statistics
    $target = $currentSite['target'] ?? null;

    if ($target !== null && isset($_GET['statistics'][$target])) {
        $inputValue = $_GET['statistics'][$target];
        if ($inputValue === '') {
            handleError(400, 'Missing input value.');
        }

        $currentState['statistics'][$target] = $inputValue;
    }

    // Redirect to the next site
    $nextSite = $currentSite['site'] ?? null;
    if ($nextSite !== null) {
        header('Location: ?site=' . $nextSite . '&' . http_build_query(['statistics' => $currentState['statistics']], '', '&', PHP_QUERY_RFC3986));
        exit();
    }
}

// Render the HTML for the current site
renderSite($currentSite, $currentState);


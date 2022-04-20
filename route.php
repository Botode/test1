<?php

/**
 * @param array $stocks - list of cities IDs with stocks
 * @param array $dists - list of distances between cities (oriented), example: [['src' => 1, 'dst' => 2, 'dist' => 15], ...]
 * @param int|string $dst - ID of destination city
 * @return array|false - 'src' => ID of nearest city with stocks,
 *                      'dist' => route distance,
 *                      'route' => sections of route, example [['src' => 1, 'dst' => 2, 'dist' => 15], ...]
 */
function getRouteFromNearestStock($stocks, $dists, $dst) {
    // prepare distanses
    $direct = [];
    $reverse = [];
    foreach ($dists as $edge) {
        if (($direct[$edge['src']][$edge['dst']] ?? INF) > $edge['dist']) {
            $direct[$edge['src']][$edge['dst']] = $edge['dist'];
            $reverse[$edge['dst']][$edge['src']] = $edge['dist'];
        }
    }

    // calc cost of cities
    $cities = [$dst => 0];
    $front = [$dst];
    $visited = [];
    $goal = null;
    while (!empty($front)) {
        $idx = 0;
        $min = INF;
        foreach ($front as $i => $id) {
            if ($cities[$id] < $min) {
                $min = $cities[$id];
                $idx = $i;
            }
        }
        $cur = $front[$idx];
        unset($front[$idx]);
        $visited[] = $cur;
        foreach ($reverse[$cur] ?? [] as $id => $dist) {
            $cost = $cities[$cur] + $dist;
            if (in_array($id, $front)) {
                if ($cities[$id] > $cost) {
                    $cities[$id] = $cost;
                }
            } elseif (!in_array($id, $visited) && ($cities[$goal] ?? INF) >= $cost) {
                $front[] = $id;
                $cities[$id] = $cost;
                if (in_array($id, $stocks)) $goal = $id;
            }
        }
    }
    if (is_null($goal)) return false;

    // calc route
    $cost = 0;
    $route = [];
    $cur = $goal;
    while ($cur != $dst) {
        foreach ($direct[$cur] as $id => $dist) {
            if ($cities[$cur] === $cities[$id] + $dist) {
                $cost += $dist;
                $route[] = ['src' => $cur, 'dst' => $id, 'dist' => $dist];
                $cur = $id;
                break;
            }
        }
    };
    return ['src' => $goal, 'dist' => $cost, 'route' => $route];

}

// Example
$stocks = [5];
$dists = [
    ['src' => 1, 'dst' => 2, 'dist' => 7],
    ['src' => 1, 'dst' => 3, 'dist' => 9],
    ['src' => 1, 'dst' => 4, 'dist' => 14],
    ['src' => 2, 'dst' => 1, 'dist' => 7],
    ['src' => 2, 'dst' => 3, 'dist' => 10],
    ['src' => 2, 'dst' => 4, 'dist' => 15],
    ['src' => 3, 'dst' => 1, 'dist' => 9],
    ['src' => 3, 'dst' => 2, 'dist' => 10],
    ['src' => 3, 'dst' => 4, 'dist' => 11],
    ['src' => 3, 'dst' => 6, 'dist' => 2],
    ['src' => 4, 'dst' => 2, 'dist' => 15],
    ['src' => 4, 'dst' => 3, 'dist' => 11],
    ['src' => 4, 'dst' => 5, 'dist' => 6],
    ['src' => 5, 'dst' => 4, 'dist' => 6],
    ['src' => 5, 'dst' => 6, 'dist' => 9],
    ['src' => 6, 'dst' => 1, 'dist' => 14],
    ['src' => 6, 'dst' => 3, 'dist' => 2],
    ['src' => 6, 'dst' => 5, 'dist' => 9],
];
$dst = 1;

$result = getRouteFromNearestStock($stocks, $dists, $dst);

var_dump($result);
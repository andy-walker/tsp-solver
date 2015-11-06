<?php

# points in universe
$points = [
    [0, 0],
    [1, 1],
    [9, 3.5],
    [6, 4.2],
    [2.3, 7.8],
    [8.3, 9.2],
    [3.8, 2.2],
    [4.4, 8.2],
    [7, 3],
    //[1.2, 9],
    [5, 5]
];

$shortestDistance = 0;
$shortestRoute    = [];

/**
 * Calculate total distance to visit all points in $route in order.
 * Will abort and return a 0 if we exceed $shortestDistance.
 */
function calculateRouteDistance($route) {
    
    global $shortestDistance;
    $distance = 0;

    for ($key=1; $key<count($route); $key++) { # quicker than foreach array_keys (8% speedup)
        
        $distance += sqrt(
            pow($route[$key][0] - $route[$key-1][0], 2) +
            pow($route[$key][1] - $route[$key-1][1], 2)
        );

        # optimization (25% speedup) - return a zero and skip calculating the 
        # remainder of the route if we've exceeded $shortestDistance
        if ($shortestDistance and $distance > $shortestDistance)
            return 0;
    
    }

    return $distance;
        
}

/**
 * Generator function based on Heap's algorithm
 * https://en.wikipedia.org/wiki/Heap%27s_algorithm
 */
function newRoute($points) {
    if (count($points) <= 1) {
        yield $points;
    } else {
        foreach (newRoute(array_slice($points, 1)) as $route) {
            foreach (range(0, count($points) - 1) as $i) {
                yield array_merge(
                    array_slice($route, 0, $i),
                    [$points[0]],
                    array_slice($route, $i)
                );
            }
        }
    }
}


# should start and end at $point[0] (home), so remove and store that.
# routes are then all the possible permutations of the remaining points
# with home as the start and end point
$home       = array_shift($points);
$start_time = microtime(true);
$count      = 0;

# iterate over all possible routes
foreach (newRoute($points) as $route) {
    
    # add start and end point
    array_unshift($route, $home);
    $route[] = $home;

    if ($distance = calculateRouteDistance($route)) {
        # update shortest distance
        if (!$shortestDistance or $distance < $shortestDistance) {
            $shortestDistance = $distance;
            $shortestRoute    = $route;
        }
    }

    $count++;

}

printf("\nChecked %s routes in %s seconds.\n", number_format($count + 1), round(microtime(true) - $start_time, 2));
printf("Shortest route: %s\n", json_encode($shortestRoute));
printf("Distance: %s units\n\n", round($shortestDistance, 2));

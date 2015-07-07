<?php

use Everyman\Neo4j\Client;
use Everyman\Neo4j\Cypher\Query;
use Everyman\Neo4j\Node;
use Everyman\Neo4j\Query\Row;
use Everyman\Neo4j\Relationship;
use Silex\Application;

require __DIR__.'/vendor/autoload.php';

$app = new Application();
$app['debug'] = true;

$neo4j = new Client();

$scores = array(
    'SANCTIONS' => -30,
    'WAR' => -50,
    'ECONOMIC_HELP' => 30,
    'MEMBER_OF' => array(
        'NATO' => 40,
        'European Union' => 25
    )
);

$app->get('/{country}', function ($country) use ($neo4j, $scores) {
        $country = mb_strtoupper($country);
        $relations = array();
        $relations = oneStepRelations($neo4j, $relations, $scores, $country);
        $relations = twoStepRelations($neo4j, $relations, $scores, $country);

        dump($relations);

        return $relations;
    });

function oneStepRelations($neo4j, $relations, $scores, $country) {
    $query = new Query($neo4j, 'MATCH (:Country {name: "' . $country . '"})-[r]-(:Country) RETURN r');
    $result = $query->getResultSet();

    /** @var Row $row */
    foreach ($result as $relationships) {
        /** @var Relationship $relationship */
        foreach ($relationships as $relationship) {
            $relationshipType = $relationship->getType();
            $targetCountry = $relationship->getEndNode()->getProperty('name');

            if (!isset($relations[$targetCountry])) {
                $relations[$targetCountry] = array(
                    'total' => 0,
                    'reasons' => array()
                );
            }

            $score = $scores[$relationshipType];
            $relations[$targetCountry]['total'] += $score;
            $relations[$targetCountry]['reasons'][] = $relationshipType . ' ' . $score;
        }
    }

    return $relations;
}

function twoStepRelations($neo4j, $relations, $scores, $country) {
    $query = new Query($neo4j, 'MATCH (n:Country {name: "' . $country . '"})-[r]->(i:Institution)<-[e]-(m:Country) RETURN i, m');
    $result = $query->getResultSet();

    /** @var Row $row */
    foreach ($result as $row) {
        /** @var Node $institution */
        $institution = $row[0];
        $institution = $institution->getProperty('name');
        /** @var Node $targetCountry */
        $targetCountry = $row[1];
        $targetCountry = $targetCountry->getProperty('name');

        if (!isset($relations[$targetCountry])) {
            $relations[$targetCountry] = array(
                'total' => 0,
                'reasons' => array()
            );
        }

        $score = $scores['MEMBER_OF'][$institution];
        $relations[$targetCountry]['total'] += $score;
        $relations[$targetCountry]['reasons'][] = 'MEMBER_OF ' . $institution . ' ' . $score;
    }

    return $relations;
}

$app->run();
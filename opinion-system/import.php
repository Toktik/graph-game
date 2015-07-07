<?php

use Everyman\Neo4j\Client;
use Everyman\Neo4j\Cypher\Query;

require __DIR__.'/vendor/autoload.php';

$neo4j = new Client();

$query = <<<QUERY
CREATE (:Country {name:'US'})
CREATE (:Country {name:'RU'})
CREATE (:Country {name:'AM'})
CREATE (:Country {name:'AZ'})
CREATE (:Country {name:'FR'})
CREATE (:Country {name:'DK'})
CREATE (:Country {name:'DE'})
CREATE (:Country {name:'GB'})
CREATE (:Country {name:'IQ'})
CREATE (:Country {name:'AF'})
CREATE (:Institution {name:'European Union'})
CREATE (:Institution {name:'NATO'})
QUERY;
$cypher = new Query($neo4j, $query);
$cypher->getResultSet();

$query = <<<QUERY
OPTIONAL MATCH (us:Country {name: 'US'}),
    (ru:Country {name: 'RU'}),
    (am:Country {name: 'AM'}),
    (az:Country {name: 'AZ'}),
    (fr:Country {name: 'FR'}),
    (dk:Country {name: 'DK'}),
    (de:Country {name: 'DE'}),
    (gb:Country {name: 'GB'}),
    (iq:Country {name: 'IQ'}),
    (af:Country {name: 'AF'}),
    (eu:Institution {name: 'European Union'}),
    (nato:Institution {name: 'NATO'})
CREATE (fr)-[:MEMBER_OF]->(eu)
CREATE (dk)-[:MEMBER_OF]->(eu)
CREATE (de)-[:MEMBER_OF]->(eu)
CREATE (fr)-[:MEMBER_OF]->(nato)
CREATE (dk)-[:MEMBER_OF]->(nato)
CREATE (de)-[:MEMBER_OF]->(nato)
CREATE (us)-[:MEMBER_OF]->(nato)
CREATE (gb)-[:MEMBER_OF]->(nato)
CREATE (us)-[:SANCTIONS]->(ru)
CREATE (us)-[:WAR]->(af)
CREATE (gb)-[:WAR]->(af)
CREATE (de)-[:WAR]->(af)
CREATE (us)-[:WAR]->(iq)
CREATE (gb)-[:WAR]->(iq)
CREATE (de)-[:WAR]->(iq)
CREATE (ru)-[:WAR]->(af)
CREATE (am)-[:WAR]->(az)
CREATE (ru)-[:ECONOMIC_HELP]->(am)
CREATE (fr)-[:SANCTIONS]->(ru)
QUERY;
$cypher = new Query($neo4j, $query);
$cypher->getResultSet();
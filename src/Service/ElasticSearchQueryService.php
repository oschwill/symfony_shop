<?php

namespace App\Service;



class ElasticSearchQueryService {

  public function __construct()
    {}

  // guter Name für eine funktion
  public function queryOne($searchTerm): array {
    $query = [
      'query' => [
          'bool' => [
              'should' => [ // Liste von Bedingungen, die optional sind aber das Ranking erhöhen
                  [
                      'multi_match' => [
                          'query' => $searchTerm,
                          'fields' => [
                              'title^2', // title hat prio
                              'description'
                          ],
                          'fuzziness' => 'AUTO'
                      ]
                  ],
                  // Prefix Query
                  [
                      'prefix' => [
                          'title' => [
                              'value' => $searchTerm,
                              'boost' => 2  // title hat prio, ist also erster treffer
                          ]
                      ]
                  ],
                  [
                      'prefix' => [
                          'description' => [
                              'value' => $searchTerm
                          ]
                      ]
                  ]
              ]
          ]
      ]
    ];

    return $query;
  }
}
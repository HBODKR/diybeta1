@chrome @en.wikipedia.beta.wmflabs.org @firefox
Feature: Sorting topics

  Background:
    Given I am on Flow page

  Scenario: Sorting
    When I sort by Newest topics
    Then it is sorted by Newest topics
    When I sort by Recently active topics
    Then it is sorted by Recently active topics

<?php

namespace Tests\Feature\Response;

use Tests\Feature\BestSellerBaseTestCase;

class BestSellerTest extends BestSellerBaseTestCase
{
    /**
     * @dataProvider versionDataProvider
     */
    public function test_best_seller_response_structure(string $version): void
    {
        $response = $this->getBestSellerApi('best-seller', $version)
            ->assertSuccessful()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonStructure([
                'success',
                'version',
                'results' => [
                    '*' => [
                        'title',
                        'description',
                        'contributor',
                        'author',
                        'contributor_note',
                        'price',
                        'age_group',
                        'publisher',
                        'primary_isbn13',
                        'primary_isbn10',
                    ]
                ]
            ]);

        $json = $response->json();

        $this->assertTrue($json['success']);
        $this->assertEquals($version, $json['version']);
        $this->assertNotEmpty($json['results']);

        $book = $json['results'][0];
        $this->assertIsString($book['title']);
        $this->assertIsString($book['author']);
        $this->assertMatchesRegularExpression('/^\d{13}$/', $book['primary_isbn13']);
        $this->assertMatchesRegularExpression('/^\d{10}$/', $book['primary_isbn10']);
        $this->assertIsInt($book['price']);

        $book = $json['results'][8];
        $this->assertEquals('WINGS OF STARLIGHT', $book['title']);
        $this->assertEquals('Clarion, the successor to the throne of Pixie Hollow, is determined to confront a monster that threatens the land. (Ages 12 to 18)', $book['description']);
        $this->assertEquals('by Allison Saft', $book['contributor']);
        $this->assertEquals('Allison Saft', $book['author']);
        $this->assertEquals('', $book['contributor_note']);
        $this->assertEquals(0, $book['price']);
        $this->assertEquals('Ages 12 to 18', $book['age_group']);
        $this->assertEquals('Disney', $book['publisher']);

        $book = $json['results'][11];
        $this->assertEquals('WINNER TAKE ALL', $book['title']);
        $this->assertEquals('An economist considers the global implications of China’s quest for metal and minerals, timber and food.', $book['description']);
        $this->assertEquals('by Dambisa Moyo', $book['contributor']);
        $this->assertEquals('Dambisa Moyo', $book['author']);
        $this->assertEquals('', $book['contributor_note']);
        $this->assertEquals(26, $book['price']);
        $this->assertEquals('', $book['age_group']);
        $this->assertEquals('Basic Books', $book['publisher']);
    }

    /**
     * @dataProvider versionDataProvider
     */
    public function test_best_seller_pagination(string $version): void
    {
        $firstPage = $this->getBestSellerApi('best-seller?offset=0', $version)
            ->assertSuccessful()
            ->json();

        $secondPage = $this->getBestSellerApi('best-seller?offset=120', $version)
            ->assertSuccessful()
            ->json();

        $this->assertNotEquals(
            $firstPage['results'][0]['title'],
            $secondPage['results'][0]['title']
        );

        $this->assertEquals('2034', $secondPage['results'][0]['title']);
    }
}

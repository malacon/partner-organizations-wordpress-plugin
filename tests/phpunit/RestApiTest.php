<?php
/**
 * Public REST API tests.
 *
 * @package PartnerOrganizations
 */

final class RestApiTest extends WP_UnitTestCase
{
    public function set_up(): void
    {
        parent::set_up();

        do_action('plugins_loaded');
        do_action('init');
        do_action('rest_api_init');
    }

    public function test_partners_endpoint_returns_envelope_with_published_partner_organizations_only(): void
    {
        $category = self::factory()->term->create_and_get([
            'taxonomy' => PartnerOrganizations\Taxonomy::SLUG,
            'name' => 'Education',
            'slug' => 'education',
        ]);
        $published_id = $this->create_partner_organization('Acme Education', 'publish');
        update_post_meta($published_id, PartnerOrganizations\MetaBoxes::WEBSITE_META_KEY, 'https://example.org');
        wp_set_object_terms($published_id, [$category->term_id], PartnerOrganizations\Taxonomy::SLUG);
        $this->create_partner_organization('Draft Partner Organization', 'draft');

        $response = rest_get_server()->dispatch(new WP_REST_Request('GET', '/partner-organizations/v1/partners'));
        $payload = $response->get_data();

        $this->assertSame(200, $response->get_status());
        $this->assertArrayHasKey('data', $payload);
        $this->assertArrayHasKey('meta', $payload);
        $this->assertCount(1, $payload['data']);
        $this->assertSame($published_id, $payload['data'][0]['id']);
        $this->assertSame('Acme Education', $payload['data'][0]['name']);
        $this->assertSame('https://example.org', $payload['data'][0]['website_url']);
        $this->assertNull($payload['data'][0]['logo']);
        $this->assertSame([
            'id' => $category->term_id,
            'name' => 'Education',
            'slug' => 'education',
        ], $payload['data'][0]['category']);
        $this->assertSame(1, $payload['meta']['page']);
        $this->assertSame(20, $payload['meta']['per_page']);
        $this->assertSame(1, $payload['meta']['total']);
        $this->assertSame(1, $payload['meta']['total_pages']);
    }

    public function test_partners_endpoint_supports_pagination_and_caps_per_page(): void
    {
        $this->create_partner_organization('Alpha', 'publish');
        $expected_id = $this->create_partner_organization('Bravo', 'publish');
        $this->create_partner_organization('Charlie', 'publish');

        $request = new WP_REST_Request('GET', '/partner-organizations/v1/partners');
        $request->set_query_params([
            'page' => 2,
            'per_page' => 1,
        ]);
        $response = rest_get_server()->dispatch($request);
        $payload = $response->get_data();

        $this->assertSame(200, $response->get_status());
        $this->assertCount(1, $payload['data']);
        $this->assertSame($expected_id, $payload['data'][0]['id']);
        $this->assertSame(2, $payload['meta']['page']);
        $this->assertSame(1, $payload['meta']['per_page']);
        $this->assertSame(3, $payload['meta']['total']);
        $this->assertSame(3, $payload['meta']['total_pages']);

        $cap_request = new WP_REST_Request('GET', '/partner-organizations/v1/partners');
        $cap_request->set_query_params(['per_page' => 101]);
        $cap_response = rest_get_server()->dispatch($cap_request);
        $this->assertSame(100, $cap_response->get_data()['meta']['per_page']);
    }

    public function test_partners_endpoint_filters_by_category_slug_and_unknown_slug_is_empty(): void
    {
        $education = self::factory()->term->create_and_get([
            'taxonomy' => PartnerOrganizations\Taxonomy::SLUG,
            'name' => 'Education',
            'slug' => 'education',
        ]);
        $corporate = self::factory()->term->create_and_get([
            'taxonomy' => PartnerOrganizations\Taxonomy::SLUG,
            'name' => 'Corporate',
            'slug' => 'corporate',
        ]);
        $expected_id = $this->create_partner_organization('Education Partner', 'publish');
        wp_set_object_terms($expected_id, [$education->term_id], PartnerOrganizations\Taxonomy::SLUG);
        $other_id = $this->create_partner_organization('Corporate Partner', 'publish');
        wp_set_object_terms($other_id, [$corporate->term_id], PartnerOrganizations\Taxonomy::SLUG);

        $request = new WP_REST_Request('GET', '/partner-organizations/v1/partners');
        $request->set_query_params(['category' => 'education']);
        $response = rest_get_server()->dispatch($request);
        $payload = $response->get_data();

        $this->assertSame(200, $response->get_status());
        $this->assertCount(1, $payload['data']);
        $this->assertSame($expected_id, $payload['data'][0]['id']);

        $unknown_request = new WP_REST_Request('GET', '/partner-organizations/v1/partners');
        $unknown_request->set_query_params(['category' => 'unknown-category']);
        $unknown_response = rest_get_server()->dispatch($unknown_request);
        $unknown_payload = $unknown_response->get_data();

        $this->assertSame(200, $unknown_response->get_status());
        $this->assertSame([], $unknown_payload['data']);
        $this->assertSame(0, $unknown_payload['meta']['total']);
        $this->assertSame(0, $unknown_payload['meta']['total_pages']);
    }

    public function test_partners_endpoint_rejects_invalid_page_and_per_page(): void
    {
        foreach ([['page' => 0], ['page' => -1], ['page' => 'abc'], ['per_page' => 0], ['per_page' => -1], ['per_page' => 'abc']] as $params) {
            $request = new WP_REST_Request('GET', '/partner-organizations/v1/partners');
            $request->set_query_params($params);
            $response = rest_get_server()->dispatch($request);

            $this->assertSame(400, $response->get_status(), 'Failed asserting params are rejected: ' . wp_json_encode($params));
        }
    }

    private function create_partner_organization(string $title, string $status): int
    {
        return self::factory()->post->create([
            'post_type' => PartnerOrganizations\PostType::SLUG,
            'post_status' => $status,
            'post_title' => $title,
        ]);
    }
}

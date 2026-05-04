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

    public function test_partners_endpoint_uses_transient_cache_until_partner_organization_changes(): void
    {
        global $wpdb;

        $post_id = $this->create_partner_organization('Cached Partner Organization', 'publish');

        $first_response = rest_get_server()->dispatch(new WP_REST_Request('GET', '/partner-organizations/v1/partners'));
        $this->assertSame('Cached Partner Organization', $first_response->get_data()['data'][0]['name']);

        $wpdb->update($wpdb->posts, ['post_title' => 'Database Only Change'], ['ID' => $post_id]);
        clean_post_cache($post_id);

        $cached_response = rest_get_server()->dispatch(new WP_REST_Request('GET', '/partner-organizations/v1/partners'));
        $this->assertSame('Cached Partner Organization', $cached_response->get_data()['data'][0]['name']);

        wp_update_post([
            'ID' => $post_id,
            'post_title' => 'Invalidated Partner Organization',
        ]);

        $invalidated_response = rest_get_server()->dispatch(new WP_REST_Request('GET', '/partner-organizations/v1/partners'));
        $this->assertSame('Invalidated Partner Organization', $invalidated_response->get_data()['data'][0]['name']);
    }

    public function test_partners_endpoint_invalidates_cache_when_partner_category_changes(): void
    {
        $category = self::factory()->term->create_and_get([
            'taxonomy' => PartnerOrganizations\Taxonomy::SLUG,
            'name' => 'Before Rename',
            'slug' => 'before-rename',
        ]);
        $post_id = $this->create_partner_organization('Categorized Partner Organization', 'publish');
        wp_set_object_terms($post_id, [$category->term_id], PartnerOrganizations\Taxonomy::SLUG);

        $request = new WP_REST_Request('GET', '/partner-organizations/v1/partners');
        $request->set_query_params(['category' => 'before-rename']);
        $first_response = rest_get_server()->dispatch($request);
        $this->assertSame('Before Rename', $first_response->get_data()['data'][0]['category']['name']);

        wp_update_term($category->term_id, PartnerOrganizations\Taxonomy::SLUG, ['name' => 'After Rename']);

        $second_request = new WP_REST_Request('GET', '/partner-organizations/v1/partners');
        $second_request->set_query_params(['category' => 'before-rename']);
        $second_response = rest_get_server()->dispatch($second_request);
        $this->assertSame('After Rename', $second_response->get_data()['data'][0]['category']['name']);
    }

    public function test_partners_endpoint_rate_limits_before_cache_lookup(): void
    {
        $_SERVER['REMOTE_ADDR'] = '198.51.100.5';
        $this->create_partner_organization('Rate Limited Partner Organization', 'publish');
        $policy = static fn (): array => ['limit' => 1, 'window' => 300];
        add_filter('partner_organizations_rate_limit_policy', $policy);

        $first_response = rest_get_server()->dispatch(new WP_REST_Request('GET', '/partner-organizations/v1/partners'));
        $second_response = rest_get_server()->dispatch(new WP_REST_Request('GET', '/partner-organizations/v1/partners'));

        remove_filter('partner_organizations_rate_limit_policy', $policy);

        $this->assertSame(200, $first_response->get_status());
        $this->assertSame(429, $second_response->get_status());
        $this->assertSame('partner_organizations_rate_limited', $second_response->get_data()['code']);
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

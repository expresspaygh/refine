<?php
require("request_filter.php");


// $filter = new RequestFilter([
//     "url" => "https://foobar.com",
//     "int" => 1
// ], [
//     'url' => 'url',
//     'int' => 'int',
// ]);

// echo "-------------------------------------------------\n";
// print_r($filter->getFilterResponse());
// echo "-------------------------------------------------\n";
// print_r($filter);
// echo "-------------------------------------------------\n";

$result = RequestFilter::check([
    "some_url" => "https://foobar.com",
    "some_email" => "someone@expresspaygh.com",
    "some_int" => 1,
    "some_bool" => "TRUE",
    "some_array" => [1, 2, 3],
    "some_ip" => "127.0.0.1",
    "some_string" => "<a href='/asdf'>foobar</a>"
], [
    'some_url' => 'url',
    'some_email' => 'email',
    'some_int' => 'int',
    "some_bool" => "bool",
    "some_array" => "array",
    "some_ip" => "ip"
]);

print_r($result);


// $filter = RequestFilter::check(get_defined_vars(), [
//     'description_html' => (!empty($description_html)) ? 'html' : 'null',
//     'process_html' => (!empty($process_html)) ? 'html' : 'null',
//     'documentation_html' => (!empty($documentation_html)) ? 'html' : 'null',
//     'website_url' => (!empty($website_url)) ? 'url' : 'null',
//     'service_delivery_type_html' => (!empty($service_delivery_type_html)) ? 'html' : 'null',
//     'post_url' => (!empty($post_url)) ? 'url' : 'null'
// ]);

// if ($filter['status'] != 0) {
//     return $filter;
// } else {
//     if (array_key_exists('output', $filter)) {
//         if (array_key_exists('ux_mda_srvrtid', $filter['output'])) $ux_mda_srvrtid = $filter['output']['ux_mda_srvrtid'];
//         if (array_key_exists('ux_category_srvrtid', $filter['output'])) $ux_category_srvrtid = $filter['output']['ux_category_srvrtid'];
//         if (array_key_exists('payment_currency_code', $filter['output'])) $payment_currency_code = $filter['output']['payment_currency_code'];
//         if (array_key_exists('name', $filter['output'])) $name = $filter['output']['name'];
//         if (array_key_exists('fee', $filter['output'])) $fee = $filter['output']['fee'];
//         if (array_key_exists('reference_code', $filter['output'])) $reference_code = $filter['output']['reference_code'];
//         if (array_key_exists('description_html', $filter['output'])) $description_html = $filter['output']['description_html'];
//         if (array_key_exists('process_html', $filter['output'])) $process_html = $filter['output']['process_html'];
//         if (array_key_exists('documentation_html', $filter['output'])) $documentation_html = $filter['output']['documentation_html'];
//         if (array_key_exists('website_url', $filter['output'])) $website_url = $filter['output']['website_url'];
//         if (array_key_exists('service_delivery_type_html', $filter['output'])) $service_delivery_type_html = $filter['output']['service_delivery_type_html'];
//         if (array_key_exists('has_process', $filter['output'])) $has_process = $filter['output']['has_process'];
//         if (array_key_exists('has_documentation', $filter['output'])) $has_documentation = $filter['output']['has_documentation'];
//         if (array_key_exists('has_service_delivery_type', $filter['output'])) $has_service_delivery_type = $filter['output']['has_service_delivery_type'];
//         if (array_key_exists('variable_rate', $filter['output'])) $variable_rate = $filter['output']['variable_rate'];
//         if (array_key_exists('min_rate_flat', $filter['output'])) $min_rate_flat = $filter['output']['min_rate_flat'];
//         if (array_key_exists('max_rate_flat', $filter['output'])) $max_rate_flat = $filter['output']['max_rate_flat'];
//         if (array_key_exists('has_additional_fees', $filter['output'])) $has_additional_fees = $filter['output']['has_additional_fees'];
//         if (array_key_exists('fee_display', $filter['output'])) $fee_display = $filter['output']['fee_display'];
//         if (array_key_exists('has_variable_fee', $filter['output'])) $has_variable_fee = $filter['output']['has_variable_fee'];
//         if (array_key_exists('min_variable_fee', $filter['output'])) $min_variable_fee = $filter['output']['min_variable_fee'];
//         if (array_key_exists('max_variable_fee', $filter['output'])) $max_variable_fee = $filter['output']['max_variable_fee'];
//         if (array_key_exists('service_delivery_time', $filter['output'])) $service_delivery_time = $filter['output']['service_delivery_time'];
//         if (array_key_exists('set_status', $filter['output'])) $set_status = $filter['output']['set_status'];
//         if (array_key_exists('post_url', $filter['output'])) $post_url = $filter['output']['post_url'];
//     }
// }

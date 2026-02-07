<?php

/**
 * Generate Complete Parent Portal Postman Collection
 * This script generates a comprehensive Postman collection including:
 * - Unified APIs (already implemented)
 * - Parent Portal APIs (from specifications)
 */

$baseUrl = "http://192.168.100.114:8088/api/v1";

$collection = [
    "info" => [
        "name" => "SmartCampus Unified API - Complete with Parent Portal",
        "description" => "Complete API collection for Unified Teacher-Guardian Mobile App with Parent Portal APIs",
        "schema" => "https://schema.getpostman.com/json/collection/v2.1.0/collection.json",
        "version" => "2.0.0"
    ],
    "variable" => [
        ["key" => "base_url", "value" => $baseUrl, "type" => "string"],
        ["key" => "teacher_token", "value" => "", "type" => "string"],
        ["key" => "guardian_token", "value" => "", "type" => "string"],
        ["key" => "current_token", "value" => "", "type" => "string"],
        ["key" => "user_type", "value" => "", "type" => "string"],
        ["key" => "student_id", "value" => "", "type" => "string"]
    ],
    "item" => []
];

// 1. Authentication Folder (Already exists)
$collection['item'][] = [
    "name" => "1. Authentication",
    "item" => [
        [
            "name" => "Teacher Login",
            "event" => [
                [
                    "listen" => "test",
                    "script" => [
                        "exec" => [
                            "pm.test('Status code is 200', function () {",
                            "    pm.response.to.have.status(200);",
                            "});",
                            "pm.test('Response has token', function () {",
                            "    var jsonData = pm.response.json();",
                            "    pm.expect(jsonData.data.token).to.exist;",
                            "    pm.collectionVariables.set('teacher_token', jsonData.data.token);",
                            "    pm.collectionVariables.set('current_token', jsonData.data.token);",
                            "});"
                        ]
                    ]
                ]
            ],

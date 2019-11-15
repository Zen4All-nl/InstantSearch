DELETE FROM admin_pages WHERE page_key='configInstantSearch' LIMIT 1;
DELETE FROM configuration WHERE configuration_key LIKE 'INSTANT_SEARCH%';
DELETE FROM configuration_group WHERE configuration_group_title = 'Instant Search Settings' LIMIT 1;
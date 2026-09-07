<?php

test('current documentation links resolve to project files', function () {
    $documents = glob(dirname(__DIR__, 2).'/docs/*.md');

    expect($documents)->not->toBeEmpty();

    foreach ($documents as $document) {
        preg_match_all('/\[[^\]]+\]\(([^)]+)\)/', file_get_contents($document), $matches);

        foreach ($matches[1] as $target) {
            if (preg_match('/^(?:https?:|mailto:|#)/', $target)) {
                continue;
            }

            $path = explode('#', $target, 2)[0];

            expect(file_exists(dirname($document).'/'.$path), $document.' -> '.$target)->toBeTrue();
        }
    }
});

test('graphify covers application classes and its analysis matches the graph', function () {
    $root = dirname(__DIR__, 2);
    $graph = json_decode(file_get_contents($root.'/graphify-out/graph.json'), true, flags: JSON_THROW_ON_ERROR);
    $analysis = json_decode(file_get_contents($root.'/graphify-out/.graphify_analysis.json'), true, flags: JSON_THROW_ON_ERROR);
    $nodeIds = array_column($graph['nodes'], 'id');
    $sourceFiles = array_column($graph['nodes'], 'source_file');

    expect($nodeIds)->not->toBeEmpty()
        ->and(count(array_unique($nodeIds)))->toBe(count($nodeIds));

    foreach (['Models', 'Actions', 'Services', 'Repositories', 'Contracts', 'Http/Controllers', 'Http/Requests', 'Http/Middleware'] as $directory) {
        foreach (glob($root.'/app/'.$directory.'/*.php') as $file) {
            $relativePath = 'app/'.$directory.'/'.basename($file);

            expect($sourceFiles, $relativePath)->toContain($relativePath);
        }
    }

    foreach ($graph['links'] ?? $graph['edges'] as $edge) {
        expect($nodeIds)->toContain($edge['source'], $edge['target']);
    }

    $analyzedNodeIds = [];

    foreach ($analysis['communities'] as $communityId => $members) {
        foreach ($members as $nodeId) {
            $analyzedNodeIds[] = $nodeId;
        }

        $graphMembers = array_column(array_filter($graph['nodes'], fn (array $node): bool => (string) $node['community'] === (string) $communityId), 'id');
        sort($members);
        sort($graphMembers);

        expect($members)->toBe($graphMembers);
    }

    sort($nodeIds);
    sort($analyzedNodeIds);

    expect($analyzedNodeIds)->toBe($nodeIds);
});

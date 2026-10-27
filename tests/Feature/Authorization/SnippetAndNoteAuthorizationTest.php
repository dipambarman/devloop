<?php

use App\Models\Note;
use App\Models\Project;
use App\Models\Snippet;
use App\Models\User;

test('user can view and update their own snippet', function () {
    $user = User::factory()->create();
    $snippet = $user->snippets()->create([
        'title' => 'My Snippet',
        'code' => 'echo "hello";',
        'language' => 'php',
    ]);

    $editResponse = $this->actingAs($user)->get(route('snippets.edit', $snippet));
    $editResponse->assertStatus(200);

    $showResponse = $this->actingAs($user)->get(route('snippets.show', $snippet));
    $showResponse->assertRedirect(route('snippets.edit', $snippet));

    $updateResponse = $this->actingAs($user)->patch(route('snippets.update', $snippet), [
        'title' => 'Updated Snippet',
        'code' => 'echo "world";',
        'language' => 'php',
    ]);
    $updateResponse->assertRedirect(route('snippets.index'));
    expect($snippet->fresh()->title)->toBe('Updated Snippet');
});

test('user cannot view or update another user snippet', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $snippet = $user->snippets()->create([
        'title' => 'Private Snippet',
        'code' => 'secret_key = 12345;',
        'language' => 'javascript',
    ]);

    $editResponse = $this->actingAs($otherUser)->get(route('snippets.edit', $snippet));
    $editResponse->assertStatus(403);

    $showResponse = $this->actingAs($otherUser)->get(route('snippets.show', $snippet));
    $showResponse->assertStatus(403);

    $updateResponse = $this->actingAs($otherUser)->patch(route('snippets.update', $snippet), [
        'title' => 'Hacked Snippet',
        'code' => 'malicious code',
        'language' => 'javascript',
    ]);
    $updateResponse->assertStatus(403);
    expect($snippet->fresh()->title)->toBe('Private Snippet');
});

test('user cannot delete another user snippet', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $snippet = $user->snippets()->create([
        'title' => 'Important Snippet',
        'code' => 'const a = 1;',
        'language' => 'javascript',
    ]);

    $response = $this->actingAs($otherUser)->delete(route('snippets.destroy', $snippet));

    $response->assertStatus(403);
    $this->assertDatabaseHas('snippets', ['id' => $snippet->id, 'deleted_at' => null]);
});

test('user can view, update and toggle pin on their own note', function () {
    $user = User::factory()->create();
    $note = $user->notes()->create([
        'title' => 'Meeting Notes',
        'content' => 'Discuss roadmap',
        'is_pinned' => false,
    ]);

    $editResponse = $this->actingAs($user)->get(route('notes.edit', $note));
    $editResponse->assertStatus(200);

    $showResponse = $this->actingAs($user)->get(route('notes.show', $note));
    $showResponse->assertRedirect(route('notes.edit', $note));

    $pinResponse = $this->actingAs($user)->patch(route('notes.togglePin', $note));
    $pinResponse->assertRedirect();
    expect($note->fresh()->is_pinned)->toBeTrue();

    $updateResponse = $this->actingAs($user)->patch(route('notes.update', $note), [
        'title' => 'Updated Meeting Notes',
        'content' => 'Discuss roadmap in detail',
    ]);
    $updateResponse->assertRedirect(route('notes.index'));
    expect($note->fresh()->title)->toBe('Updated Meeting Notes');
});

test('user cannot view, update or toggle pin on another user note', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $note = $user->notes()->create([
        'title' => 'Confidential Note',
        'content' => 'Top secret strategy',
        'is_pinned' => false,
    ]);

    $editResponse = $this->actingAs($otherUser)->get(route('notes.edit', $note));
    $editResponse->assertStatus(403);

    $showResponse = $this->actingAs($otherUser)->get(route('notes.show', $note));
    $showResponse->assertStatus(403);

    $pinResponse = $this->actingAs($otherUser)->patch(route('notes.togglePin', $note));
    $pinResponse->assertStatus(403);
    expect($note->fresh()->is_pinned)->toBeFalse();

    $updateResponse = $this->actingAs($otherUser)->patch(route('notes.update', $note), [
        'title' => 'Defaced Title',
    ]);
    $updateResponse->assertStatus(403);
    expect($note->fresh()->title)->toBe('Confidential Note');
});

test('user cannot delete another user note', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $note = $user->notes()->create([
        'title' => 'Important Note',
        'content' => 'Do not delete',
    ]);

    $response = $this->actingAs($otherUser)->delete(route('notes.destroy', $note));

    $response->assertStatus(403);
    $this->assertDatabaseHas('notes', ['id' => $note->id, 'deleted_at' => null]);
});

test('user cannot attach a snippet to a project they do not have access to (IDOR protection)', function () {
    $owner = User::factory()->create();
    $attacker = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);

    $response = $this->actingAs($attacker)->post(route('snippets.store'), [
        'title' => 'Sneaky Snippet',
        'code' => 'const x = 10;',
        'language' => 'javascript',
        'project_id' => $project->id,
    ]);

    $response->assertStatus(403);
    $this->assertDatabaseMissing('snippets', ['title' => 'Sneaky Snippet']);
});

test('user cannot attach a note to a project they do not have access to (IDOR protection)', function () {
    $owner = User::factory()->create();
    $attacker = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);

    $response = $this->actingAs($attacker)->post(route('notes.store'), [
        'title' => 'Sneaky Note',
        'content' => 'Infiltrating project notes',
        'project_id' => $project->id,
    ]);

    $response->assertStatus(403);
    $this->assertDatabaseMissing('notes', ['title' => 'Sneaky Note']);
});

<?php

it('sends visitors of the root url to the dashboard', function () {
    $response = $this->get('/');

    $response->assertRedirect('/dashboard');
});

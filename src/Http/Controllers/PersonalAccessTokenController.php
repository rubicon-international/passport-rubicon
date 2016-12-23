<?php

namespace Laravel\Passport\Http\Controllers;

use App\User;
use App\Relationship;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\PersonalAccessTokenResult;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;

class PersonalAccessTokenController
{
    /**
     * The validation factory implementation.
     *
     * @var ValidationFactory
     */
    protected $validation;

    /**
     * Create a controller instance.
     *
     * @param  ValidationFactory  $validation
     * @return void
     */
    public function __construct(ValidationFactory $validation)
    {
        $this->validation = $validation;
    }

    /**
     * Get all of the personal access tokens for the authenticated user.
     *
     * @param  Request  $request
     * @return Response
     */
    public function forUser(Request $request)
    {
        return $request->user()->tokens->load('client')->filter(function ($token) {
            return $token->client->personal_access_client && ! $token->revoked;
        })->values();
    }

    /**
     * Create a new personal access token for the user.
     *
     * @param  Request  $request
     * @return PersonalAccessTokenResult
     */
    public function store(Request $request)
    {
        // Validate the token input
        $this->validation->make($request->all(), [
            'name' => 'required|unique:oauth_access_tokens|max:255',
            'scopes' => 'array|in:'.implode(',', Passport::scopeIds()),
        ])->validate();

        // Send a request for a new token
        $newToken = $request->user()->createToken(
            $request->name, $request->scopes ?: []
        );

        // Get the new token primary key for building the relationship
        $tokenPrimaryKey = DB::table('oauth_access_tokens')->where('name', $request->name)->value('primary_key');

        // Create the token Relationship
        Relationship::create([
            'token_key'             => $tokenPrimaryKey,
            'api_token_type'        => $request->api_token_type,
            'api_client_id'         => $request->api_client_id,
            'api_application_id'    => $request->api_application_id,
        ]);

        //Return the new token
        return $newToken;
    }

    /**
     * Delete the given token.
     *
     * @param  Request  $request
     * @param  string  $tokenId
     * @return Response
     */
    public function destroy(Request $request, $tokenId)
    {
        if (is_null($token = $request->user()->tokens->find($tokenId))) {
            return new Response('', 404);
        }

        $token->revoke();
    }
}

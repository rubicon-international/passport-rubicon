<style scoped>
    .action-link {
        cursor: pointer;
    }

    .m-b-none {
        margin-bottom: 0;
    }
    .radio-container.last {
        margin-bottom: 1em;
    }
    .label {
        margin: 0 .5em .5em 0;
    }
    @media(min-width: 992px) {
        .radio-container {
            margin: 4px 0 0 14.2em;
        }
    }
</style>

<template>
    <div>
        <div>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span>
                            Personal Access Tokens
                        </span>

                        <a class="action-link" @click="showCreateTokenForm">
                            Create New Token
                        </a>
                    </div>
                </div>

                <div class="panel-body">
                    <!-- No Tokens Notice -->
                    <p class="m-b-none" v-if="tokens.length === 0">
                        You have not created any personal access tokens.
                    </p>

                    <!-- Personal Access Tokens -->
                    <div class="table-responsive">
                        <table class="table table-borderless m-b-none table-striped table-hover table-condensed data-table" v-if="tokens.length > 0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Scopes</th>
                                    <!--
                                    <th>Token Type</th>
                                    <th>API Client ID</th>
                                    <th>API Application ID</th>
                                    -->
                                    <th>Created</th>
                                    <th>Expires</th>
                                    <th>Status</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr v-for="token in tokens">
                                    <!-- Client Name -->
                                    <td style="vertical-align: middle;">
                                        {{ token.name }}
                                    </td>

                                    <!-- Token Scopes -->
                                    <td style="vertical-align: middle;">
                                        <span class="label label-info" v-for="scope in token.scopes">
                                            {{ scope }}
                                        </span>
                                    </td>

                                    <!--
                                    <td style="vertical-align: middle;">
                                        Token Types
                                    </td>
                                    <td style="vertical-align: middle;">
                                        API Client ID
                                    </td>

                                    <td style="vertical-align: middle;">
                                        API Application ID
                                    </td>
                                    -->

                                    <!-- Token Creation Date -->
                                    <td style="vertical-align: middle;">
                                        {{ token.created_at }}
                                    </td>

                                    <!-- Token Expiration Date -->
                                    <td style="vertical-align: middle;">
                                        {{ token.expires_at }}
                                    </td>

                                    <!-- Delete Button -->
                                    <td style="vertical-align: middle;">
                                        <div v-if="token.revoked" class="text-small text-danger">
                                            Revoked
                                        </div>
                                        <div v-else>
                                            <a class="btn btn-warning btn-xs" @click="revoke(token)">
                                                Revoke
                                            </a>
                                        </div>
                                    </td>

                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Token Modal -->
        <div class="modal fade" id="modal-create-token" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button " class="close" data-dismiss="modal" aria-hidden="true">&times;</button>

                        <h4 class="modal-title">
                            Create Token
                        </h4>
                    </div>

                    <div class="modal-body">
                        <!-- Form Errors -->
                        <div class="alert alert-danger" v-if="form.errors.length > 0">
                            <p><strong>Ahhh Snaps!</strong> Something went wrong!</p>
                            <br>
                            <ul>
                                <li v-for="error in form.errors">
                                    {{ error }}
                                </li>
                            </ul>
                        </div>

                        <!-- Create Token Form -->
                        <form class="form-horizontal" role="form" @submit.prevent="store">
                            <!-- Name -->
                            <div class="form-group">
                                <label class="col-md-4 control-label" for="create-token-name">Token Name</label>

                                <div class="col-md-6">
                                    <input id="create-token-name" type="text" class="form-control" name="name" v-model="form.name" placeholder="Token Name">
                                </div>
                            </div>

                            <!-- Application client ID -->
                            <div class="form-group">
                                <label class="col-md-4 control-label" for="api_client_id">Client ID</label>
                                <div class="col-md-6">
                                    <input v-model="form.api_client_id" id="api_client_id" class="form-control" placeholder="Client ID">
                                </div>
                            </div>

                            <!-- Application ID -->
                            <div class="form-group">
                                <label class="col-md-4 control-label" for="api_application_id">Application ID</label>
                                <div class="col-md-6">
                                    <input v-model="form.api_application_id" id="api_application_id" class="form-control" placeholder="Application ID">
                                </div>
                            </div>

                            <!-- Application Token Type -->
                            <label class="col-md-4 control-label" for="api_token_type" style="padding-left: 0;">Token Type</label>
                            <div class="radio-container">
                                <input type="radio" id="api_token_type_uber" value="uber" v-model="form.api_token_type">
                                <label for="api_token_type_uber">Live</label>
                            </div>
                            <div class="radio-container last">
                                <input type="radio" id="api_token_type_dev" value="dev" v-model="form.api_token_type">
                                <label for="api_token_type_dev">Development</label>
                            </div>

                            <!-- Scopes -->
                            <div class="form-group" v-if="scopes.length > 0">
                                <label class="col-md-4 control-label">Scopes / Access</label>
                                <div class="col-md-6">
                                    <div v-for="scope in scopes">
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" @click="toggleScope(scope.id)" :checked="scopeIsAssigned(scope.id)">
                                                {{ scope.id }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Modal Actions -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>

                        <button type="button" class="btn btn-primary" @click="store">
                            Create
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Access Token Modal -->
        <div class="modal fade" id="modal-access-token" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button " class="close" data-dismiss="modal" aria-hidden="true">&times;</button>

                        <h4 class="modal-title">
                            Personal Access Token
                        </h4>
                    </div>

                    <div class="modal-body">
                        <p>
                            Here is your new personal access token. This is the only time it will be shown so don't lose it!
                            You may now use this token to make API requests.
                        </p>

                        <pre><code>{{ accessToken }}</code></pre>
                    </div>

                    <!-- Modal Actions -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>


                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        /*
         * The component's data.
         */
        data() {
            return {
                accessToken: null,
                tokens: [],
                scopes: [],
                form: {
                    name: '',
                    scopes: [],
                    api_client_id: '',
                    api_application_id: '',
                    api_token_type: '',
                    errors: []
                }
            };
        },

        /**
         * Prepare the component (Vue 1.x).
         */
        ready() {
            this.prepareComponent();
        },

        /**
         * Prepare the component (Vue 2.x).
         */
        mounted() {
            this.prepareComponent();
        },

        methods: {
            /**
             * Prepare the component.
             */
            prepareComponent() {
                this.getTokens();
                this.getScopes();

                $('#modal-create-token').on('shown.bs.modal', () => {
                    $('#create-token-name').focus();
                });
            },

            /**
             * Get all of the personal access tokens for the user.
             */
            getTokens() {
                this.$http.get('/oauth/personal-access-tokens')
                        .then(response => {
                            this.tokens = response.data;
                        });
            },

            /**
             * Get all of the available scopes.
             */
            getScopes() {
                this.$http.get('/oauth/scopes')
                        .then(response => {
                            this.scopes = response.data;
                        });
            },

            /**
             * Show the form for creating new tokens.
             */
            showCreateTokenForm() {
                $('#modal-create-token').modal('show');
            },

            /**
             * Create a new personal access token.
             */
            store() {
                this.accessToken = null;

                this.form.errors = [];

                this.$http.post('/oauth/personal-access-tokens', this.form)
                        .then(response => {
                            this.form.name;
                            this.form.api_client_id;
                            this.form.api_application_id = '';
                            this.form.api_token_type = '';
                            this.form.scopes = [];
                            this.form.errors = [];
                            this.tokens.push(response.data.token);
                            this.showAccessToken(response.data.accessToken);
                        })
                        .catch(response => {
                            if (typeof response.data === 'object') {
                                this.form.errors = _.flatten(_.toArray(response.data));
                            } else {
                                this.form.errors = ['Something went wrong. Please try again.'];
                            }
                        });

            },

            /**
             * Toggle the given scope in the list of assigned scopes.
             */
            toggleScope(scope) {
                if (this.scopeIsAssigned(scope)) {
                    this.form.scopes = _.reject(this.form.scopes, s => s == scope);
                } else {
                    this.form.scopes.push(scope);
                }
            },

            /**
             * Determine if the given scope has been assigned to the token.
             */
            scopeIsAssigned(scope) {
                return _.indexOf(this.form.scopes, scope) >= 0;
            },

            /**
             * Show the given access token to the user.
             */
            showAccessToken(accessToken) {
                $('#modal-create-token').modal('hide');

                this.accessToken = accessToken;

                $('#modal-access-token').modal('show');
            },

            /**
             * Revoke the given token.
             */
            revoke(token) {
                this.$http.delete('/oauth/personal-access-tokens/' + token.id)
                        .then(response => {
                            this.getTokens();
                        });
            }

        }

    }

</script>
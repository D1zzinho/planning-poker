<template>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    Active users
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item" v-for="user in users">{{ user.name }}</li>
                </ul>
            </div>
        </div>


        <div class="py-5 mb-5 mx-auto bg-light rounded-3">
            <div class="container-fluid mb-5">
                <div id="votes" class="votes">
                    <div class="wrapper">
                        <div class="vote-card ">
                            <div class="front" data-value=""></div>
                            <div class="back"></div>
                        </div>
                        <div class="name"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid mb-5">
            <div class="votes-show" style="text-align: center">
                <div class="row">
                    <div class="col-4">
                        Result
                        <div id="vote-result"></div>
                    </div>
                    <div class="col-4">
                        <button type="button" class="btn btn-lg btn-info">Show votes</button>
                        <button type="button" class="btn btn-lg btn-danger">Reset</button>
                    </div>
                    <div class="col-4">

                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid mb-5">
            <div id="vote" class="vote">
                <div class="vote-card" data-value="1">
                    <div class="front">1</div>
                </div>
                <div class="vote-card" data-value="2">
                    <div class="front">2</div>
                </div>
                <div class="vote-card" data-value="3">
                    <div class="front">3</div>
                </div>
                <div class="vote-card" data-value="5">
                    <div class="front">5</div>
                </div>
                <div class="vote-card" data-value="8">
                    <div class="front">8</div>
                </div>
                <div class="vote-card" data-value="13">
                    <div class="front">13</div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
export default {
    props: [
        'user'
    ],

    data() {
        return {
            votes: [],
            vote: {},
            users: []
        }
    },

    created() {
        Echo.join('game')
            .here(user => {
                this.users = user;
            })
            .joining(user => {
                this.users.push(user);
            })
            .leaving(user => {
                this.users = this.users.filter(u => {
                    return u.id !== user.id;
                })
            })
    },

    mounted() {
        console.log('Component mounted.')
    }
}
</script>

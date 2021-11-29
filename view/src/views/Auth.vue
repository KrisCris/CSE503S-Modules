<template>
    <form class="sign" @submit.prevent="auth">
        <h1>Welcome!</h1>
        <el-alert
            title="Error"
            type="error"
            :description="errorMsg"
            show-icon
            v-if="errorMsg"
            :closable="false"
            @click="errorMsg=''"
        >
        </el-alert>

        <label>Username:</label>
        <input type="text" v-model="username" required />
        <p v-if="invalidUsername" class="error">{{ invalidUsername }}</p>

        <label>Password:</label>
        <input type="password" v-model="password" required />
        <p v-if="invalidPassword" class="error">{{ invalidPassword }}</p>

        <div v-show="showInvitation">
            <label>Invitation Code:</label>
            <input type="text" v-model="invitationCode" />
        </div>
        

        <el-radio-group v-model="action">
            <el-radio-button label="Sign In"></el-radio-button>
            <el-radio-button label="Sign Up"></el-radio-button>
        </el-radio-group>
        <div class="submit">
            <button>CONTINUE</button>
        </div>
    </form>
</template>

<script>
import { GET, POST } from "../requests.js";

export default {
    name: "Auth",
    data() {
        return {
            regex: /^[\w_\.\-]+$/,
            username: "",
            password: "",
            action: "Sign In",
            userInfo: undefined,
            invalidPassword: "",
            errorMsg: "",
            invitationCode: ''
        };
    },
    methods: {
        auth() {
            let api =
                this.action == "Sign In" ? "/users/login" : this.invitationCode ? "/users/register/"+this.invitationCode : "/users/register";
            if (
                this.username &&
                this.password &&
                this.regex.test(this.username)
            ) {
                POST(api, {
                    username: this.username,
                    password: this.password,
                }).then((res) => {
                    if (res.code == 1) {
                        this.$router.push({ name: "Home" });
                    } else {
                        this.errorMsg = res.msg;
                    }
                });
            } else {
                this.errorMsg = "Invalid Input";
            }
        },
    },
    mounted() {
        GET("/users/").then((res) => {
            if (res.code == 1) {
                this.$router.push({ name: "Home" });
            }
        });
    },
    computed: {
        showInvitation(){
            if(this.action == "Sign Up"){
                return true
            }
            return false
        },
        invalidUsername() {
            if (!this.regex.test(this.username) && !this.username == "") {
                return "Invalid Username!";
            } else return " ";
        },
    },
};
</script>

<style>
.sign {
    max-width: 420px;
    margin: 5rem auto;
    background: white;
    text-align: left;
    padding: 40px;
    border-radius: 2rem;

}
.sign .radio {
    margin-top: 1rem;
}
.sign label {
    color: #aaa;
    display: inline-block;
    margin: 25px 0 15px;
    font-size:0.8em;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: bold;
}

.sign button {
    background: #0b6dff;
    border: 0;
    padding: 10px 20px;
    margin-top: 20px;
    color: white;
    border-radius: 20px;
}

.sign button:hover {
    background: #217aff;
    border: 0;
    padding: 10px 20px;
    margin-top: 20px;
    color: white;
    border-radius: 20px;
}

.sign .submit {
    text-align: center;
}

.sign input {
    display: block;
    padding: 10px 6px;
    width: 100%;
    box-sizing: border-box;
    border: none;
    border-bottom: 1px solid #ddd;
    color: #555;
}
.sign .error {
    color: #ff0062;
    margin-top: 10px;
    font-size: 0.8em;
    font-weight: bold;
}
</style>
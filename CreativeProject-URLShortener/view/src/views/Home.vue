<template>
    <Nav></Nav>
    <div id="home">
        <div class="noti">
            <el-alert
                v-if="errorMsg"
                :title="errorMsg"
                type="error"
                effect="dark"
                :closable="false"
                @click="errorMsg=''"
            >
            </el-alert>
        </div>
        <form id="homeForm" @submit.prevent="handleSubmit">
            <input
                type="text"
                v-model="originalURL"
                placeholder="Paste a link here"
            />
            <el-button type="primary" round @click="handleSubmit"
                >Short URL</el-button
            >
        </form>
        <div class="noti">
            <el-alert v-show="shortURL" :title="shortURL" type="success" effect="dark" :closable="false" @click="shortURL=''"> </el-alert>
        </div>
    </div>
</template>

<script>
import { GET, POST, PUT, PATCH, DELETE } from "../requests.js";
import Nav from "../components/Nav.vue";

export default {
    name: "Home",
    components: { Nav },
    data() {
        return {
            baseUrl: "",
            originalURL: "",
            errorMsg: "",
            shortURL: "",
        };
    },
    methods: {
        handleSubmit() {
            let url = this.originalURL.toLowerCase();
            if (!url.startsWith("https://") && !url.startsWith("http://")) {
                this.originalURL = "http://" + this.originalURL;
            }
            PUT("/links/", {
                link: this.originalURL,
            }).then((res) => {
                if (res.code == 1) {
                    this.updateClipboard(this.baseUrl + "/" + res.data.shortened)
                    this.shortURL = this.baseUrl + "/" + res.data.shortened + " is copied to your clipboard!";
                } else {
                    if(res.code==0){
                        this.$router.push({ name: "Auth" });
                    } else {
                        this.errorMsg = res.msg;
                    }
                }
            });
        },
        updateClipboard(newClip) {
            let that = this
            navigator.clipboard.writeText(newClip).then(function() {
                that.successMsg="Copied "+newClip
            }, function() {
                errorMsg="No permission to access clipboard!"
            });
        }
    },
    mounted() {
        // check login status
        GET("/users/").then((res) => {
            if (res.code == -1) {
                this.$router.push({ name: "Auth" });
            } else if (res.code == -3) {
                POST("/user/refresh", {}).then(() => {
                    window.location.reload;
                });
            }
        });

        let url = window.location.origin.toLowerCase();
        if (url.startsWith("https://")) {
            url = url.substring(8, url.length);
        } else if (url.startsWith("http://")) {
            url = url.substring(7, url.length);
        }
        this.baseUrl = url;
    },
};
</script>
<style>
#home {
    margin-top: 8rem;
}
#homeForm {
    margin: 2rem;
}
#homeForm input {
    width: 40%;
    height: 3rem;
    border-radius: 1.5rem;
    padding: 0px 1rem;
    font-size: 1.5rem;
    border: none;
    margin-right: 1rem;
}
#homeForm button {
    height: 3rem;
    border-radius: 1.5rem;
}
.noti {
    width: 45%;
    margin: 0px auto;
    font-weight: bold;
}
</style>
<template>
    <div id="redirect">
        <div v-if="!errorMsg">
            <h1>Redirecting</h1>
            <h3>TO</h3>
            <a v-bind:href="url"><h2>{{ url }}</h2></a>
            <h3>in {{ countDown }} Seconds</h3>
        </div>
        <div v-else>
            <h1>{{errorMsg}}</h1>
            <el-button type="primary" round @click="$router.push({ name: 'Home' });">Back</el-button>
        </div>

    </div>

</template>

<script>
import { GET, POST, PUT, PATCH, DELETE } from "../requests.js";
export default {
    name: "Redirect",
    props: ['urlKey'],
    data() {
        return {
            // urlKey: this.$route.params.urlKey,
            url: "",
            countDown: 3,
            timeout: undefined,
            interval: undefined,
            errorMsg: ""
        };
    },
    mounted() {
        if (this.urlKey) {
            // valid length
            if (this.urlKey.length >= 4) {
                // fetch url
                GET('/links/'+this.urlKey).then((res)=>{
                    if(res.code == 1){
                        this.url = res.data.link
                        // timer
                        this.timeout = setTimeout(() => {
                            window.location.replace(this.url);
                        }, 3000);
                        // count down
                        this.interval = setInterval(() => {
                            if (this.countDown > 0) {
                                this.countDown--;
                            } else {
                                clearInterval(this.interval);
                            }
                        }, 1000);
                    } else {
                        this.errorMsg = "Link " + res.msg
                    }
                })
                this.url = "http://youtube.com";
            } else {
                this.errorMsg = "Invalid Link!";
            }
        }
    },
    unmounted(){
        if(this.timeout){
            clearTimeout(this.timeout)
        }
        if(this.interval){
            clearInterval(this.interval)
        }
    }
};
</script>

<style>
#redirect{
    margin-top: 10%;
    color: white;
}
#redirect a{
    text-decoration: none;
    color:rgb(0, 150, 209)
}
</style>
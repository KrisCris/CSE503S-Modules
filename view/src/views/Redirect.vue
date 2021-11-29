<template>
    <h1>Redirecting</h1>
    <h2>{{urlKey}}</h2>
    <p>TO</p>
    <a v-bind:href="url"><h2>{{ url }}</h2></a>
    <p>{{ countDown }}</p>
</template>

<script>
export default {
    name: "Redirect",
    props: ['urlKey'],
    data() {
        return {
            // urlKey: this.$route.params.urlKey,
            url: "",
            countDown: 5,
            timeout: undefined,
            interval: undefined
        };
    },
    mounted() {
        if (this.urlKey) {
            // valid length
            if (this.urlKey.length >= 4) {
                // fetch url
                this.url = "http://youtube.com";

                // timer
                this.timeout = setTimeout(() => {
                    window.location.replace(this.url);
                }, 5000);
                // count down
                this.interval = setInterval(() => {
                    if (this.countDown > 0) {
                        this.countDown--;
                    } else {
                        clearInterval(this.interval);
                    }
                }, 1000);
            } else {
                // 404
                this.countDown = "invalid url!";
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
</style>
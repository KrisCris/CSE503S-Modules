const app = Vue.createApp({
    data() {
        return {
            header2:"This is the header",
            condition:false,
            x:0,
            y:0,
            numbers:[
                {'number':1, 'increased':false},
                {'number':2, 'increased':false},
                {'number':3, 'increased':false},
                {'number':4, 'increased':false},
                {'number':5, 'increased':false},
                {'number':6, 'increased':false},
            ],
            attrBind:"https://www.baidu.com"
        }
    },
    methods:{
        changeTitle(title=undefined) {
            if(title){
                this.header2=title
            } else {
                this.header2="changed"
            }
        },
        invertCondition() {
            this.condition = !this.condition
        },
        handleEvent(e, data){
            console.log(e, e.type)
            if (data){
                console.log(data)
            }
        },
        handleMousemove(e){
            this.x = e.offsetX
            this.y = e.offsetY
        },
        increase(obj){
            obj.number++
            obj.increased=true
        }
    },
    // computed properties: 
    // still data, but like a func, and
    // depend on data in the template, that change, this will change
    computed:{
        increasedNumber(){
            // filter methods: true keep, false remove

            // return this.numbers.filter((number)=>{
            //     return number.increased
            // })

            return this.numbers.filter((number)=>number.increased)
        }
    }
})

app.mount('#app')
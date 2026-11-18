<template>

    <!-- TRACER CONFIGURATION PAGE -->
    <div class="sticky-top sidebar mb-3" v-if="isPageTracerConfiguration">

        <!-- MENU -->
        <div class="card sidebar-card mb-3">
            <ul class="nav flex-column" id="sidebarMenuList">

                <!-- Tracer settings-->
                <li class="nav-item pb-1 pt-1">
                    <a class="nav-link nav-link-child-style" href="#tracerSettings">{{$translations.tracerConfiguration.tracerSettings.title}}</a>
                </li>

                <!-- LensSOAPTracer Settings-->
                <li class="nav-item pb-1 pt-1">
                    <a class="nav-link nav-link-child-style" href="#lensSoapTracerSettings">{{$translations.tracerConfiguration.lensSoapTracerSettings.title}}</a>
                </li>

            </ul>
        </div>
    </div>

    <!-- ALL OTHER PAGES -->
    <div class="sticky-top sidebar mb-3" v-else>
        
        <!-- MENU -->
        <div class="card sidebar-card mb-3">
            <ul class="nav flex-column" id="sidebarMenuList">

                <!-- BASIC DATA -->
                <li class="nav-item pb-1 pt-1" id="basicDataId">
                    <router-link to="/newOrder" @click="to('/newOrder')" class="nav-link nav-link-style">{{$translations.newOrder.basicData.title}}</router-link>
                </li>

                <!-- DETAILS -->
                <li class="nav-item pb-1 pt-1" id="detailsId" >
                    <router-link to="/details" @click="to('/details')" class="nav-link nav-link-style" :disabled="$store.getters.canContinueToDetails ? undefined : 'true' ">{{$translations.newOrder.details.title}}</router-link>
                    <div 
                        v-for="component in $store.getters.getOrderedDetailsComponents" 
                        :value="component" 
                        :key="component.sequence"
                    >
                        <a 
                            v-if="component.show"
                            :disabled="component.enabled && $store.getters.canContinueToDetails ? undefined : 'true' "
                            :class="linkStyle(component)" 
                            @click="onLinkClick(component)"
                            :href="'#' + component.name">{{component.title}}
                            
                        </a>
                    </div>
                </li>
            </ul>

        </div>

        <!-- EDITING ORDER INFO -->
        <div class="card sidebar-card mb-3" v-if="orders.isEditing">
            <div>
                <label>{{$translations.newOrder.editingOrder.title}}</label>
                <hr>
                <div class="row justify-content-start">
                    <div class="col-auto my-3">
                        <label class="placeholder-text d-flex">{{$translations.newOrder.editingOrder.orderNumber}}</label>
                        <label class="w-100">{{orders.editingOrder.OrdNumb}}</label>
                    </div>
                    <div class="col-auto my-3">
                        <label class="placeholder-text d-flex">{{$translations.newOrder.editingOrder.reference}}</label>
                        <label class="w-100">{{orders.editingOrder.Reference}}</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>


<script>
export default {
    data() {
        return {
            componentSelector: {
                selectedComponent: null,
                foundComponents: [],
                wasComponentFound: false,
                ordersWatcherTimeout: null,
                visibilityChangeTimeout: null
            },
        }
    },
    computed: {
        orders() {return this.$store.state.moduleOrders},
        isPageTracerConfiguration() {return this.$route.name == "tracerConfiguration"}
    },
    watch: {
        '$route': function () {this.componentSelectorSequence()},
        'orders': {
            handler() {
                if (this.componentSelector.ordersWatcherTimeout) clearTimeout(this.componentSelector.ordersWatcherTimeout)
                this.componentSelector.ordersWatcherTimeout = setTimeout(() => {this.componentSelectorSequence()}, 100);
            },
            deep: true
        }
    },
    mounted() {
        this.componentSelectorSequence()
    },
    unmounted() {
        this.removeEventListener()
        this.resetVariables()
    },
    methods: {
        componentSelectorSequence() {
            this.removeEventListener()
            this.resetVariables()
            this.$nextTick(() => {
                this.getComponents()
                this.addEventListeners()
                this.triggerEvents()
            });
        },
        resetVariables() {
            this.componentSelector = {
                selectedComponent: null,
                foundComponents: [],
                wasComponentFound: false,
                ordersWatcherTimeout: null,
                visibilityChangeTimeout: null
            }
        },
        getComponents() {
            // Get HTML elements of components using id names
            this.$store.getters.getOrderedDetailsComponents.forEach(component => {
                const componentNode = document.getElementById(component.name)
                if (!componentNode) return

                this.componentSelector.foundComponents.push({component: component, node: componentNode})
            });
        },
        removeEventListener(){
            // Remove all binded event handlers
            this.componentSelector.foundComponents.forEach(component => {
                window.removeEventListener('DOMContentLoaded', component.handler, false);
                window.removeEventListener('load', component.handler, false);
                window.removeEventListener('scroll', component.handler, false);
                window.removeEventListener('resize', component.handler, false);
            })
        },
        addEventListeners() {
            // Bind the component-visibility-detection event handler to HTML component elements
            this.componentSelector.foundComponents = this.componentSelector.foundComponents.map(component => {
                let t = this
                component.handler = this.onDetectComponentVisibility(component.node, function() {
                    t.onComponentInView(component.component)
                });
                window.addEventListener('DOMContentLoaded', component.handler, false);
                window.addEventListener('load', component.handler, false);
                window.addEventListener('scroll', component.handler, false);
                window.addEventListener('resize', component.handler, false);
                return component
            })
        },
        triggerEvents() {
            // Manually trigger the component-visibility-detection function
            this.componentSelector.foundComponents.forEach(component => {
                let t = this
                this.onDetectComponentVisibility(component.node, function() {
                    t.onComponentInView(component.component)
                })()
            })
        },
        onDetectComponentVisibility(el, callback) {
            let t = this
            return function () {

                // Compare elements bounds with current viewport bounds to detect if element is in viewport
                const isElementInViewport = (element, topOffset, bottomOffset) => {
                    const rect = element.getBoundingClientRect()
                    return (
                        rect.top - topOffset >= 0 || 
                        (window.innerHeight || document.documentElement.clientHeight) - rect.bottom - bottomOffset <= 0
                    )
                }
                
                // If element is in viewport, set found flag to true
                const visible = isElementInViewport(el, 10, 200);
                if (typeof callback == 'function' && visible) {
                    if (!t.componentSelector.wasComponentFound){
                        t.componentSelector.wasComponentFound = true
                        callback(); 
                    }

                    if (t.componentSelector.visibilityChangeTimeout) clearTimeout(t.componentSelector.visibilityChangeTimeout)
                    t.componentSelector.visibilityChangeTimeout = setTimeout(() => {t.componentSelector.wasComponentFound = false}, 1);
                }
            }
        },
        onComponentInView(component) {
            this.componentSelector.selectedComponent = component
        },
        onLinkClick(component) {
            this.to('/details')

            setTimeout(() => {
               this.componentSelector.selectedComponent = component 
            }, 10)
        },
        to(route){
            this.$router.push(route)
        },
        isComponentSelectable(component) {
            return component && component !== undefined && component.enabled && component.show
        },
        isComponentSelected(component) {
            return (
                component && component !== undefined &&
                this.componentSelector.selectedComponent && 
                this.componentSelector.selectedComponent !== undefined && 
                this.componentSelector.selectedComponent.name == component.name && 
                this.isComponentSelectable(component)
            )
        },
        linkStyle(component){
            let style = "nav-link nav-link-child-style"
            if (this.isComponentSelected(component)){
                style += " selected"
            }
            return style
        },
    }
}
</script>

<style scoped>
.sticky-top {
    top:10%;
}
</style>
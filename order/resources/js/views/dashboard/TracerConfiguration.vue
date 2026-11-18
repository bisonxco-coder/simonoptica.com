<template>

    <!-- Tracer settings -->
    <div class="card pt-3 pb-4 px-5 mb-3" id="tracerSettings">
        <h4 class="panel-title pt-4 pb-3">{{$translations.tracerConfiguration.tracerSettings.title}}</h4>

        <div class="row">

            <!-- Tracer -->
            <div class="col-12">
                <panel-select
                    :placeholder="$translations.tracerConfiguration.tracerSettings.placeholders.tracer"
                    v-model="tracerSettings.selectedTracer"
                    :selection="tracerSettings.tracerSelection"
                    :notNull="true"
                    titleParameter="tracerName"
                    :disabled="tracerSettings.isTracerDisabled"
                />
            </div>

            <!-- VCA / OMA -->
            <div class="col-12">
                <div class="d-flex mb-4">
                    <input
                        id="tracerConfiguration_isVca"
                        v-model="tracerSettings.isVca"
                        class="form-check-input panel-checkbox"
                        type="checkbox"
                        :disabled="tracerSettings.isVcaDisabled"
                    />
                    <label class="form-check-label ms-4 px-3 position-absolute" for="tracerConfiguration_isVca">
                        {{$translations.tracerConfiguration.tracerSettings.placeholders.isVca}}
                    </label>
                </div>
            </div>

            <!-- Mode -->
            <div class="col-12">
                <panel-select
                    :placeholder="$translations.tracerConfiguration.tracerSettings.placeholders.mode"
                    v-model="tracerSettings.selectedMode"
                    :selection="tracerSettings.modeSelection"
                    titleParameter="label"
                    :preserveOnDisable="true"
                    :disabled="tracerSettings.isModeDisabled"
                />
            </div>

            <!-- Port -->
            <div class="col-12 mb-4">
                <panel-select
                    class="pb-2"
                    v-if="tracerSettings.hasPortSelection"
                    :placeholder="$translations.tracerConfiguration.tracerSettings.placeholders.port"
                    v-model="tracerSettings.selectedPort"
                    :selection="tracerSettings.portSelection"
                    :preserveOnDisable="true"
                    :disabled="tracerSettings.isPortDisabled"
                    style="margin-bottom: 0 !important"
                />
                <panel-input
                    class="pb-2"
                    v-else
                    :placeholder="$translations.tracerConfiguration.tracerSettings.placeholders.port"
                    v-model="tracerSettings.selectedPort"
                    :preserveOnDisable="true"
                    :disabled="false"
                    style="margin-bottom: 0 !important"
                />
                <span>
                    {{$translations.tracerConfiguration.tracerSettings.portSubText}}
                </span>
            </div>

            <!-- Baud -->
            <div class="col-12">
                <panel-select
                    :placeholder="$translations.tracerConfiguration.tracerSettings.placeholders.baud"
                    v-model="tracerSettings.selectedBaud"
                    :selection="tracerSettings.baudSelection"
                    :preserveOnDisable="true"
                    :disabled="tracerSettings.isBaudDisabled"
                />
            </div>

            <!-- Parity -->
            <div class="col-12">
                <panel-select
                    :placeholder="$translations.tracerConfiguration.tracerSettings.placeholders.parity"
                    v-model="tracerSettings.selectedParity"
                    :selection="tracerSettings.paritySelection"
                    titleParameter="label"
                    :preserveOnDisable="true"
                    :disabled="tracerSettings.isParityDisabled"
                />
            </div>

            <!-- Data bit -->
            <div class="col-12">
                <panel-select
                    :placeholder="$translations.tracerConfiguration.tracerSettings.placeholders.dataBit"
                    v-model="tracerSettings.selectedDataBit"
                    :selection="tracerSettings.dataBitSelection"
                    :preserveOnDisable="true"
                    :disabled="tracerSettings.isDataBitDisabled"
                />
            </div>

            <!-- Stop bit -->
            <div class="col-12">
                <panel-select
                    :placeholder="$translations.tracerConfiguration.tracerSettings.placeholders.stopBit"
                    v-model="tracerSettings.selectedStopBit"
                    :selection="tracerSettings.stopBitSelection"
                    :preserveOnDisable="true"
                    :disabled="tracerSettings.isStopBitDisabled"
                />
            </div>

            <!-- Flow control -->
            <div class="col-12">
                <panel-select
                    :placeholder="$translations.tracerConfiguration.tracerSettings.placeholders.flowControl"
                    v-model="tracerSettings.selectedFlowControl"
                    :selection="tracerSettings.flowControlSelection"
                    titleParameter="label"
                    :preserveOnDisable="true"
                    :disabled="tracerSettings.isFlowControlDisabled"
                />
            </div>

            <!-- Some padding -->
            <div class="col-12 pb-5" />

            <!-- Tracer Configuration (tracerIP) -->
            <div class="col-12 mb-2">
                <panel-input
                    :placeholder="$translations.tracerConfiguration.tracerSettings.placeholders.tracerIp"
                    v-model="tracerSettings.tracerIp"
                    :disabled="tracerSettings.isTracerIpDisabled"
                />
            </div>

            <!-- Serial number -->
            <div class="col-12">
                <panel-input
                    :placeholder="$translations.tracerConfiguration.tracerSettings.placeholders.serialNumber"
                    v-model="tracerSettings.serialNumber"
                    :disabled="tracerSettings.isSerialNumberDisabled"
                />
            </div>
        </div>
    </div>


    <!-- LensSOAPTracer Settings -->
    <div class="card pt-3 pb-4 px-5 mb-3" id="lensSoapTracerSettings">
        <h4 class="panel-title pt-4 pb-3">{{$translations.tracerConfiguration.lensSoapTracerSettings.title}}</h4>

        <div class="row">

            <!-- Configuration LensSOAPTracer -->
            <div class="col-12 mb-5">
                <panel-input
                    class="pb-2"
                    :placeholder="$translations.tracerConfiguration.lensSoapTracerSettings.placeholders.configurationLensSoapTracer"
                    v-model="lensSoapTracerSettings.ip"
                    :disabled="lensSoapTracerSettings.isIpDisabled"
                    style="margin-bottom: 0 !important"
                />
                <span style="white-space: pre-line">
                    {{$translations.tracerConfiguration.lensSoapTracerSettings.configurationLensSoapTracerSubText}}
                </span>
            </div>

            <div class="col-12" id="lensSoapTracerDownload">
                <button class="d-flex expand-button border-0 p-0" @click="onOpenDownloadLensSoapTracerPopupClick">
                    {{$translations.tracerConfiguration.lensSoapTracerSettings.downloadLensSOAPTracer}}
                </button>
            </div>
        </div>

        <!-- POPUP - Download LensSOAPTracer -->
        <panel-popup :isOpen="lensSoapTracerPopup.isOpened" @onClose="onCloseLensSoapTracerPopup" :parentStyle="'max-height: 100%; overflow-y: auto;'">
            <h1>LensSOAPTracer</h1>
            <ol>
                <li>{{$translations.tracerConfiguration.lensSoapTracerDownload.downloadWindows}} <a href="./LensSOAPTracer/LensSOAPTracerQT.exe" target="_blank" class="click-here-link">{{$translations.tracerConfiguration.lensSoapTracerDownload.here}}</a>.</li>
                <li>{{$translations.tracerConfiguration.lensSoapTracerDownload.downloadMac}} <a href="./LensSOAPTracer/LensSOAPTracerQT.zip" target="_blank" class="click-here-link">{{$translations.tracerConfiguration.lensSoapTracerDownload.here}}</a>.</li>
                <li>{{$translations.tracerConfiguration.lensSoapTracerDownload.manual3}}!</li>
                <li>{{$translations.tracerConfiguration.lensSoapTracerDownload.manual4}}.</li>
                <li>{{$translations.tracerConfiguration.lensSoapTracerDownload.manual5}}:</li>
                <ul>
                    <li>{{$translations.tracerConfiguration.lensSoapTracerDownload.manual51}}.</li>
                    <li>{{$translations.tracerConfiguration.lensSoapTracerDownload.manual52}}.</li>
                    <li>{{$translations.tracerConfiguration.lensSoapTracerDownload.manual53}}.</li>
                </ul>
                <li>{{$translations.tracerConfiguration.lensSoapTracerDownload.manual6}}:</li>
                <ul>
                    <li>{{$translations.tracerConfiguration.lensSoapTracerDownload.manual61}}.</li>
                    <li>{{$translations.tracerConfiguration.lensSoapTracerDownload.manual62}}.</li>
                    <ul>
                        <li>{{$translations.tracerConfiguration.lensSoapTracerDownload.manual63}}</li>
                        <li>{{$translations.tracerConfiguration.lensSoapTracerDownload.manual64}}</li>
                        <li>{{$translations.tracerConfiguration.lensSoapTracerDownload.manual65}}.</li>
                    </ul>
                </ul>
            </ol>
        </panel-popup>
    </div>

</template>

<script>
import PanelInput from '@/components/PanelInput.vue'
import PanelSelect from '@/components/PanelSelect.vue'
import PanelPopup from '@/components/PanelPopup.vue'
import Tracer from '@/utils/tracer.js'

export default {
    components: {
        PanelInput, PanelSelect, PanelPopup
    },
    data() {
        return {
            lensSoapTracerPopup: {
                isOpened: false
            },
        }
    },
    computed: {
        tracer() {return this.$store.state.moduleTracer},
        tracerSettings() {return this.$store.state.moduleTracer.tracerSettings},
        lensSoapTracerSettings() {return this.$store.state.moduleTracer.lensSoapTracerSettings}
    },
    watch: {
        "tracerSettings.selectedTracer": function (newTracerDefinition) {
            if (this.tracer.canSave)
                this.setDefaultValuesFromTracerDefinition(newTracerDefinition)
        },
        "tracerSettings.selectedMode": function () {
            if (this.tracer.canSave)
                this.setTracerMode()
        }
    },
    async mounted() {
        this.tracer.canSave = false

        // Set dashboard title
        this.$store.commit('setDashboardTitle', {
            title: () => this.$translations.tracerConfiguration.title,
            subTitle: ''
        })

        try {
            await this.getTracerDefinitions()
            let gp = await this.getComPorts()
            if(gp === 'LensSoapTracerMissing'){
                let element = document.getElementById("lensSoapTracerDownload");
                element.scrollIntoView({behavior: "smooth", block: "end"});
            }
            await this.getTracerConfiguration()
            this.loadTracerConfiguration()
            this.setTracerMode()
        } catch (error) {console.error(error)}

        this.$nextTick(() => {
            this.tracer.canSave = true
        });
    },
    methods: {
        onCloseLensSoapTracerPopup() {
            this.lensSoapTracerPopup.isOpened = false
        },
        onOpenDownloadLensSoapTracerPopupClick() {
            this.lensSoapTracerPopup.isOpened = true
        },
        getTracerConfiguration() {
            return Tracer.getTracerConfiguration()
        },
        getTracerDefinitions() {
            return Tracer.getTracerDefinitions()
        },
        getComPorts() {
            return Tracer.getComPorts()
        },
        setDefaultValuesFromTracerDefinition(tracerDefinition) {
            Tracer.setDefaultValuesFromTracerDefinition(tracerDefinition)
        },
        setTracerMode() {
            Tracer.tracerModeChanged()
        },
        loadTracerConfiguration () {
            Tracer.loadTracerConfiguration()
        }
    }
}
</script>

<style lang="scss" scoped>
.click-here-link {
    color: red !important;
    font-size: 14px !important;
}
</style>

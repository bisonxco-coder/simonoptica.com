import RecType from '@/JSON/RecType.json'

function getCustomRecType() {
    let result = []
    const customFiles = require.context('./custom', true, /[A-Za-z0-9-_,\s]+\.json$/i)
    customFiles.keys().forEach(async file => {
        result = customFiles(file)
    })
    return result
}
const customRecType = getCustomRecType()


function getKey(name, resource) {
    let values = RecType[name]
    try {
        if (resource[name] !== undefined && resource[name])
        values = resource[name]
    } catch (error) {}

    return values
}

export default {
    get Rectype() {return getKey('Rectype', customRecType)},
    get Material() {return getKey('Material', customRecType)},
    get Index() {return getKey('Index', customRecType)},
    get Special() {return getKey('Special', customRecType)},
    get Group() {return getKey('Group', customRecType)},
    get Type() {return getKey('Type', customRecType)},
    get Usage() {return getKey('Usage', customRecType)},
    get Design() {return getKey('Design', customRecType)},
}
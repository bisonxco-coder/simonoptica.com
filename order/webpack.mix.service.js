function getFiles(relativePathOfPluginRoot, regexFileSearch) {
    const fs = require('fs')
    const fullPath = __dirname + "/" + relativePathOfPluginRoot

    if (!fs.existsSync(fullPath)) {
        return []
    } 

    const getAllFilesInFolder = (dir, files_) => {
        try {
            files_ = files_ || [];
            var files = fs.readdirSync(dir);
            for (var i in files) {
                var name = dir + '/' + files[i];
                if (fs.statSync(name).isDirectory()) {
                    getAllFilesInFolder(name, files_);
                } else {
                    files_.push(name);
                }
            }
            return files_;
        } catch (error) {
            return []
        }
    }

    return getAllFilesInFolder(fullPath)
        .filter(x => regexFileSearch.test(x))
        .map(x => relativePathOfPluginRoot + x.split('/' + relativePathOfPluginRoot).pop())
}

module.exports = { getFiles };
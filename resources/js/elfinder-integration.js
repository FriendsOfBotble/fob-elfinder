const elfinderDefault = (open) => ({
    title: 'File Manager',
    cssAutoLoad: false,
    baseUrl : './',
    url: ELFINDER_CONNECTOR_URL,
    lang: ELFINDER_LANG || 'en',
    startPathHash: open ? open : void(0),
    useBrowserHistory: false,
    autoOpen: false,
    width: '80%',
    height: '80%',
    commandsOptions: {
        getfile: {
            oncomplete: 'close',
            multiple: true,
        },
    },
    rememberLastDir: true,
})

const elfinderOpen = (instance, open, resolve, reject) => {
    if (open) {
        if (!Object.keys(instance.files()).length) {
            instance.one('open', () => {
                instance.file(open)? resolve(instance) : reject(instance, 'errFolderNotFound');
            });
        } else {
            new Promise((res, rej) => {
                if (instance.file(open)) {
                    res();
                } else {
                    instance.request({cmd: 'parents', target: open}).done(e => {
                        instance.file(open)? res() : rej();
                    }).fail(() => {
                        rej();
                    });
                }
            }).then(() => {
                instance.exec('open', open).done(() => {
                    resolve(instance);
                }).fail(err => {
                    reject(instance, err? err : 'errFolderNotFound');
                });
            }).catch((err) => {
                reject(instance, err? err : 'errFolderNotFound');
            });
        }
    } else {
        resolve(instance);
    }
}

document.addEventListener('core-editor-init', function(event) {
    if (! ELFINDER_EDITOR) {
        return
    }

    const editor = event.detail

    editor.ckFinderUsing((editor) => {
        const uploadTargetHash = 'l1_Lw'
        const ckfinder = editor.commands.get('ckfinder'),
            fileRepo = editor.plugins.get('FileRepository'),
            ntf = editor.plugins.get('Notification'),
            i18n = editor.locale.t,
            insertImages = urls => {
                const imgCmd = editor.commands.get('imageUpload')

                if (!imgCmd.isEnabled) {
                    ntf.showWarning(i18n('Could not insert image at the current position.'), {
                        title: i18n('Inserting image failed'),
                        namespace: 'ckfinder',
                    })
                    return
                }

                editor.execute('imageInsert', { source: urls })
            }

        const elfinderInstance = (open) => {
            return new Promise((resolve, reject) => {
                if (_elfinderInstance) {
                    elfinderOpen(_elfinderInstance, open, resolve, reject);
                } else {
                    _elfinderInstance = $('<div/>').dialogelfinder({
                        ...elfinderDefault(open),
                        getFileCallback : (files, _instance) => {
                            let imgs = [];
                            _instance.getUI('cwd').trigger('unselectall');
                            $.each(files, function(i, f) {
                                if (f && f.mime.match(/^image\//i)) {
                                    imgs.push(f.url);
                                } else {
                                    editor.execute('link', f.url);
                                }
                            });
                            if (imgs.length) {
                                insertImages(imgs);
                            }
                        }
                    }).elfinder('instance');

                    elfinderOpen(_elfinderInstance, open, resolve, reject);
                }
            });
        };

        let _elfinderInstance = null

        if (ckfinder) {
            ckfinder.execute = () => {
                elfinderInstance().then(fm => {
                    fm.getUI().dialogelfinder('open')
                })
            }
        }

        const uploder = function(loader) {
            let upload = function(file, resolve, reject) {
                elfinderInstance(uploadTargetHash).then(fm => {
                    let fmNode = fm.getUI()
                    fmNode.dialogelfinder('open')
                    fm.exec('upload', { files: [file], target: uploadTargetHash }, void (0), uploadTargetHash)
                        .done(data => {
                            if (data.added && data.added.length) {
                                fm.url(data.added[0].hash, { async: true }).done(function(url) {
                                    resolve({
                                        'default': url,
                                    })
                                    fmNode.dialogelfinder('close')
                                }).fail(function() {
                                    reject('errFileNotFound')
                                })
                            } else {
                                reject(fm.i18n(data.error ? data.error : 'errUpload'))
                                fmNode.dialogelfinder('close')
                            }
                        })
                        .fail(err => {
                            const error = fm.parseError(err)
                            reject(fm.i18n(error ? (error === 'userabort' ? 'errAbort' : error) : 'errUploadNoFiles'))
                        })
                }).catch((fm, err) => {
                    const error = fm.parseError(err)
                    reject(fm.i18n(error ? (error === 'userabort' ? 'errAbort' : error) : 'errUploadNoFiles'))
                })
            }

            this.upload = function() {
                return new Promise(function(resolve, reject) {
                    if (loader.file instanceof Promise || (loader.file && typeof loader.file.then === 'function')) {
                        loader.file.then(function(file) {
                            upload(file, resolve, reject)
                        })
                    } else {
                        upload(loader.file, resolve, reject)
                    }
                })
            }
            this.abort = function() {
                _elfinderInstance && _elfinderInstance.getUI().trigger('uploadabort')
            }
        }

        fileRepo.createUploadAdapter = loader => {
            return new uploder(loader)
        }
    })
})

if (ELFINDER_REPLACE_DEFAULT_MEDIA) {
    window.RvMediaCustomCallback = (selector, options) => {
        window.rvMedia = window.rvMedia || {}

        let $body = $('body')

        let defaultOptions = {
            multiple: true,
            type: '*',
            onSelectFiles: (files, $el) => {
            },
        }

        options = $.extend(true, defaultOptions, options)

        let _elfinderInstance = null

        const elfinderInstance = (open, $current) => {
            return new Promise((resolve, reject) => {
                if (_elfinderInstance) {
                    elfinderOpen(_elfinderInstance, open, resolve, reject);
                } else {
                    _elfinderInstance = $('<div/>').dialogelfinder({
                        ...elfinderDefault(open),
                        getFileCallback: (files, _instance) => {
                            let selectedFiles = [];

                            _instance.getUI('cwd').trigger('unselectall');

                            if (options.filter === 'image') {
                                $.each(files, function(i, f) {
                                    if (f && f.mime.match(/^image\//i)) {
                                        selectedFiles.push(f);
                                    }
                                });
                            } else {
                                selectedFiles = [...files]
                            }

                            selectedFiles = selectedFiles.map(file => {
                                let f = {
                                    ...file,
                                    full_url: file.url,
                                }

                                f.type = 'document'

                                if (f.mime.match(/^image\//i)) {
                                    f.thumb = file.url
                                    f.type = 'image'
                                }

                                if (f.mime.match(/^video\//i)) {
                                    f.type = 'video'
                                }

                                if (f.mime.match(/^text\//i)) {
                                    f.type = 'document'
                                }

                                const regex = new RegExp(`^\/${ELFINDER_BASEPATH}\/`, 'i')
                                f.url = f.url.replace(regex, '/');

                                return f
                            })

                            if (selectedFiles.length) {
                                options.onSelectFiles(selectedFiles, $current);
                            }
                        }
                    }).elfinder('instance');

                    elfinderOpen(_elfinderInstance, open, resolve, reject);
                }
            });
        };

        let clickCallback = (event) => {
            event.preventDefault()

            let $current = $(event.currentTarget)

            elfinderInstance(null, $current).then(fm => {
                fm.getUI().dialogelfinder('open')
            })
        }

        if (typeof selector === 'string') {
            $body.off('click', selector).on('click', selector, clickCallback)
        } else {
            selector.off('click').on('click', clickCallback)
        }
    }
}

<div>
    @if ($finalFile)
    <script>
        console.log('finalFile: ' + '{{ $finalFile->getFilename() }}');
        document.getElementById('data.video').value = '{{ $finalFile->getFilename() }}';
    </script>
    finalFile: {{ $finalFile->getFilename() }}

    
@endif
    <input type="file" id="videoFile" />
    <button type="button" id="submit" onclick="uploadChunks()">Submit</button>
    <script>
        let chunksName = [];

        function uploadChunks() {
            const file = document.querySelector('#videoFile').files[0];
            if (!file) {
                return;
            }

            @this.set('fileName', file.name, true);
            @this.set('fileSize', file.size, true);

            $chunks = getAllChunks(file);
            if ($chunks.length === 0) {
                return;
            }

            uploadFile(file, $chunks);
        }

        function getAllChunks(file) {
            const chunks = [];
            let start = 0;

            while (start < file.size) {
                const chunkEnd = Math.min(start + @js($chunkSize), file.size);
                chunks.push([start, chunkEnd]);
                start = chunkEnd;
            }

            return chunks;
        }

        function uploadFile(file, chunks) {
            counter = 0;
            chunks.forEach((item) => {
                let start = item[0];
                let end = item[1];
                let chunk = file.slice(start, end);

                tryToUploadChunk(chunk, counter);
                counter++;
            });

            console.log('chunks: ' + chunksName);
        }

        function tryToUploadChunk(chunk, counter) {
            @this.upload('fileChunk', chunk, (uploadedFilename) => {
                @this.set('uploads.'+counter+'.chunkName', uploadedFilename );
                console.log('uploadedFilename: ' + uploadedFilename + ' counter: ' + counter);
            }, () => {
                console.log('error');
                let _time = Math.floor((Math.random() * 20000) + 1);
                setTimeout(tryToUploadChunk, _time, chunk, counter);
            }, (event) => {
            });
        }







        function livewireUploadChunk(file, start) {
            console.log('livewireUploadChunk: ' + start);
            // Get chunk from start
            const chunkEnd = Math.min(start + @js($chunkSize), file.size);
            if (chunkEnd >= file.size) {
                console.log('chunkEnd > file.size');
                setTimeout(() => {
                    console.log(file.size + ' done');
                }, 300000000000);
            }

            console.log('chunkEnd: ' + chunkEnd);
            const chunk = file.slice(start, chunkEnd);

            @this.upload('fileChunk', chunk, (uName) => {}, () => {}, (event) => {
                console.log('progress: ' + event.detail.progress);
                if (event.detail.progress == 100) {
                    // We recursively call livewireUploadChunk from within itself
                    start = chunkEnd;
                    console.log('start: ' + start + ' file.size: ' + file.size);
                    if (start == file.size) {
                        console.log('start == file.size');
                        return;
                    }

                    if (start < file.size) {
                        console.log(start + ' < ' + file.size + ' calling livewireUploadChunk');
                        let _time = Math.floor((Math.random() * 20000) + 1);
                        setTimeout(livewireUploadChunk, _time, file, start);

                        livewireUploadChunk(file, start);
                    }
                }
            });
        }
    </script>
</div>

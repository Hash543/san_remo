define(['jquery'], function($) {
    function sortMediaData(inputData) {
        $.each(inputData, function(index, value) {
            if (value['position'] && value['position'] != index && parseInt(value['position']) < inputData.length) {
                changeItemPosition(inputData, value, index);
            }

        });
        function changeItemPosition(inputData, value, index, recursion) {
            recursion = recursion || false;
            if (inputData[value['position']] || recursion) {
                var tmp = inputData[value['position']];
                inputData[value['position']] = value;
                if(typeof(tmp) != 'undefined' && tmp['position']) {
                    changeItemPosition(inputData, tmp, index, true);
                } else {
                    inputData[index] = tmp;
                }
            } else {
                inputData[value['position']] = value;
            }
        }
    }
    
    function _comparePosition(a,b) {
        if (parseInt(a.position )< parseInt(b.position))
            return -1;
        if (parseInt(a.position) > parseInt(b.position))
            return 1;
        return 0;
    }

    return function(originalWidget){
        $.widget(
            'mage.AddFotoramaVideoEvents',
            $['mage']['AddFotoramaVideoEvents'],
            {
                _createVideoData: function (inputData, isJSON) {
                    var fotoramaData = this.fotoramaItem.data('fotorama').data;
                    fotoramaData = fotoramaData.sort(_comparePosition);
                    $.each(inputData, function(inputIndex, input){
                        $.each(fotoramaData, function(index, value){
                            if (input.videoUrl && value['videoUrl'] === input.videoUrl) {
                                input.position = index;
                            }
                        });
                    });
                    sortMediaData(inputData);
                    return this._super(inputData, isJSON);
                }
            });
        return $['mage']['AddFotoramaVideoEvents'];
    };
});
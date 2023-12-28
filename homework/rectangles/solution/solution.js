function maxFreeRect(width, height, rects) {
    let bestRect = { left: 0, top: 0, width: 0, height: 0 };
    let histogram = Array(width).fill(0);

    for (let row = 0; row < height; row++) {
        updateHistogramForRow(histogram, row, rects, width);
        let stack = [];
        for (let column = 0; column<= width; column++) {
            while (stack.length > 0 && (column === width || histogram[stack[stack.length - 1]] > histogram[column])) {
                let height = histogram[stack.pop()];
                let left = stack.length > 0 ? stack[stack.length - 1] + 1 : 0;
                let area = height * (column - left);

                if (area > bestRect.width * bestRect.height) {
                    bestRect = { left, top: row - height + 1, width: column - left, height };
                }
            }

            if (column < width) {
                stack.push(column);
            }
        }
    }

    return bestRect;
}

function updateHistogramForRow(histogram, row, rects, width) {
    for (let column = 0; column< width; column++) {
        let isFree = true;
        for (let rect of rects) {
            if (column >= rect.left && column< rect.left + rect.width && row >= rect.top && row < rect.top + rect.height) {
                isFree = false;
                break;
            }
        }
        histogram[column] = isFree ? histogram[column] + 1 : 0;
    }
}

module.exports = { maxFreeRect };




// In nodejs, this is the way how export is performed.
// In browser, module has to be a global varibale object.
module.exports = { maxFreeRect };

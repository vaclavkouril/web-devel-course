const IntervalTree = require('interval-tree');

function maxFreeRect(width, height, rects) {
  // Initialize interval trees for columns and rows
  const columnTree = new IntervalTree(1,{});
  console.log("help");
  const rowTree = new IntervalTree(1,{});
  
  // Insert the entire area as a free rectangle
  columnTree.add(0, width, 0);
  rowTree.add(0, height, 0);

  // Remove occupied rectangles from the interval trees
  rects.forEach(rect => {
    columnTree.remove(rect.left, rect.left + rect.width);
    rowTree.remove(rect.top, rect.top + rect.height);
  });

  // Find the largest free rectangle
  let maxArea = 0;
  let maxRect = null;

  columnTree.forEachNode(columnNode => {
    rowTree.forEachNode(rowNode => {
      const intersection = {
        left: columnNode.start,
        top: rowNode.start,
        right: columnNode.end,
        bottom: rowNode.end,
      };

      const area = (intersection.right - intersection.left) * (intersection.bottom - intersection.top);

      if (area > maxArea) {
        maxArea = area;
        maxRect = intersection;
      }
    });
  });

  // Return the computed rectangle or an empty rectangle if none is found
  return maxRect ? { left: maxRect.left, top: maxRect.top, width: maxRect.right - maxRect.left, height: maxRect.bottom - maxRect.top } : { left: 0, top: 0, width: 0, height: 0 };
}

module.exports = { maxFreeRect };

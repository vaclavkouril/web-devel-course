function preprocessGalleryData(imgData) {
	concatSimilarArrays(imgData);

	let blocks = findSimilar(imgData);


    blocks.forEach(block => sortBlock(block, imgData));


    sortBlocks(blocks, imgData);


    let allBlocks = concatNonSimilarV2(blocks, imgData);


	return allBlocks;


}




function concatSimilarArrays(imgData){
	imgData.forEach(image => {
		if (!image.hasOwnProperty('simcp')) {
				image.simcp = [...image.similar];

			}
		const arr = image.simcp;
		arr.forEach(simImg => {
			if (!simImg.hasOwnProperty('simcp')) {
				simImg.simcp = [...simImg.similar];

			}
			let similar =[...image.simcp,...simImg.simcp, image, simImg];

			
			simImg.simcp = [...new Set(similar)];
			image.simcp  = [...new Set(similar)];



		});


	});


}



function findSimilar(imgData) {

    let blocks = [];

    let visited = new Set();


    imgData.forEach(image => {

        if (!visited.has(image)) {

            let block = new Set([image]);

            visited.add(image);



            let queue = Array.from(image.simcp);

            while (queue.length > 0) {

                let similarImage = queue.shift();

                if (!visited.has(similarImage)) {

                    block.add(similarImage);

                    visited.add(similarImage);

                    queue.push(...similarImage.simcp.filter(img => !visited.has(img)));

                }

            }



            blocks.push(Array.from(block));

        }

    });


    return blocks;

}




function sortBlock(block, imgData) {


    block.sort((a, b) => {


        let timeComparison = a.created.getTime() - b.created.getTime();


        return timeComparison !== 0 ? timeComparison : imgData.indexOf(a) - imgData.indexOf(b);


    });


}




function concatNonSimilarV2(blocks, imgData){


	let consolidatedBlocks = [];


    let currentBlock = [];



    blocks.forEach(block => {


        if (block.length === 1) {


            currentBlock.push(block[0]);


        } else {


            if (currentBlock.length > 0) {


                consolidatedBlocks.push(currentBlock);


                currentBlock = [];


            }


            consolidatedBlocks.push(block);


        }


    });


    if (currentBlock.length > 0) {


        consolidatedBlocks.push(currentBlock);


    }


    return consolidatedBlocks;


}



function sortBlocks(blocks, imgData) {


    blocks.sort((a, b) => {


        let timeComparison = a[0].created.getTime() - b[0].created.getTime();


        return timeComparison !== 0 ? timeComparison : imgData.indexOf(a[0]) - imgData.indexOf(b[0]);


    });


}


// format for proper export


module.exports = { preprocessGalleryData };



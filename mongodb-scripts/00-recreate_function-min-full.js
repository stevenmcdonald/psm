function recreate(src, pipeline, index, dest, merge) {
    let start = Date.now();
    if(merge) {
        last_op = {
            $merge: {
                into: 'tmp'
            }
        };
    } else {
        last_op = {
            $out: 'tmp'
        };
    }
    pipeline.push(last_op);
    src.aggregate(pipeline, {
        allowDiskUse: true
    });

    if(!Array.isArray(index)) {
        index = [index];
    }

    print('creating indexes');
    for(let i in index) {
        print(index[i]);
        db.tmp.ensureIndex(index[i]);
    }

    if(dest != 'tmp') {
        let start2 = Date.now();
        db.tmp.renameCollection(dest, true);
        print('renamed ' + dest + ' in ' + (Date.now() - start2) + ' milliseconds');
    }
    print('recreated ' + dest + ' in ' + (Date.now() - start) + ' milliseconds');
}


let data = null;
// await download of data
await fetch("miserables.json")
.then(function(response) {
    return response.json()
})
.then(function(json) {
    data = json;
});

const container = document.getElementById("sigma-container");
// graphology.umd.min.js should expose a global variable named "graphology"
const graph = new graphology.Graph();
graph.import(data);

console.log(GraphologyLayout);

GraphologyLayout.random.assign(graph, {scale:100});

GraphologyLayoutForceatlas2.assign(
    graph,
    {
        iterations: 100,
        settings: {
          gravity: 10
        }
    }
);
/*
GraphologyLayoutNoverlap.assign(
    graph,
    {
        maxIterations: 20,
        settings: {
          ratio: 0.2
        }
    }
)
*/
const renderer = new Sigma(graph, container);


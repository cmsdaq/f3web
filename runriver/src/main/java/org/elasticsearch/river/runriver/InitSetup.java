package org.elasticsearch.river.runriver;

//ELASTICSEARCH
import org.elasticsearch.client.Client;
import org.elasticsearch.action.get.GetResponse;


class InitSetup {

    static void prepareServer(Client client, String runIndex) {
        //runindexCheck(client,runIndex);
        createStreamMapping(client,runIndex);
        createStateMapping(client,runIndex);

    }



    static void runindexCheck(Client client, String runIndex){
        client.admin().cluster().prepareHealth().setWaitForYellowStatus().execute().actionGet();
        if (!client.admin().indices().prepareExists(runIndex).execute().actionGet().isExists()) {
            client.admin().indices()
                .prepareCreate(runIndex)
                .execute().actionGet();
        }
    }



    static void createStateMapping(Client client, String runIndex){
        client.admin().cluster().prepareHealth().setWaitForYellowStatus().execute().actionGet();
//        GetResponse response = client.prepareGet("runindex", "state-hist", "_mapping").setRefresh(true).execute().actionGet();
//        if (response.isExists()){ return; }

        String stateHistMapping = "{\"state-hist\":{\"_parent\":{\"type\":\"run\"},\"_timestamp\":{\"enabled\":\"true\",\"store\":\"yes\"},\"properties\":{\"hmini\":{\"type\":\"object\",\"properties\":{\"key\":{\"type\":\"integer\"},\"count\":{\"type\":\"float\"}}},\"hmicro\":{\"type\":\"object\",\"properties\":{\"key\":{\"type\":\"integer\"},\"count\":{\"type\":\"float\"}}},\"hmacro\":{\"type\":\"object\",\"properties\":{\"key\":{\"type\":\"integer\"},\"count\":{\"type\":\"float\"}}}}}}";
        client.admin().indices().preparePutMapping()
            .setIndices(runIndex)
            .setType("state-hist")
            .setSource(stateHistMapping)
            .execute().actionGet();
    }

    static void createStreamMapping(Client client, String runIndex){
        client.admin().cluster().prepareHealth().setWaitForYellowStatus().execute().actionGet();
//        GetResponse response = client.prepareGet("runindex", "stream-hist", "_mapping").setRefresh(true).execute().actionGet();
//        if (response.isExists()){ return; }

        String streamHistMapping = "{\"stream-hist\":{\"_parent\":{\"type\":\"run\"},\"_timestamp\":{\"enabled\":\"true\",\"store\":\"yes\"},\"properties\":{\"stream\":{\"type\":\"string\",\"index\" : \"not_analyzed\"},\"ls\":{\"type\":\"integer\"},\"in\":{\"type\":\"float\"},\"out\":{\"type\":\"float\"},\"filesize\":{\"type\":\"float\"}}}}";
        client.admin().indices().preparePutMapping()
            .setIndices(runIndex)
            .setType("stream-hist")
            .setSource(streamHistMapping)
            .execute().actionGet();
    }

}

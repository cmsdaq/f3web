package org.elasticsearch.river.runriver;


//JAVA
import java.io.IOException;
import java.util.*;


//ELASTICSEARCH
import org.elasticsearch.client.Client;
import org.elasticsearch.action.get.GetResponse;
import org.elasticsearch.action.index.IndexResponse;
import org.elasticsearch.action.search.SearchResponse;
import org.elasticsearch.search.SearchHit;
import org.elasticsearch.search.SearchHits;
import org.elasticsearch.common.inject.Inject;
import org.elasticsearch.action.admin.indices.mapping.put.PutMappingRequestBuilder;
import org.elasticsearch.action.admin.indices.mapping.put.PutMappingResponse;

//RIVER
import org.elasticsearch.river.River;
import org.elasticsearch.river.RiverName;
import org.elasticsearch.river.RiverSettings;

//jsonBuilder
import org.elasticsearch.common.xcontent.support.XContentMapValues;
import static org.elasticsearch.common.xcontent.XContentFactory.*;

//org.json
import net.sf.json.JSONObject;
import net.sf.json.JSONSerializer;
import org.apache.commons.io.IOUtils;

public class RunMonitor extends AbstractRunRiverThread {
        


    public RunMonitor(RiverName riverName, RiverSettings settings, Client client) {
        super(riverName,settings,client);
        InitSetup.prepareServer(client,runIndex_write);
    }

    @Override
    public void beforeLoop(){
        logger.info("RunMonitor Started v1.3.0");
        this.interval = polling_interval;
    }
    public void afterLoop(){
        logger.info("RunMonitor Stopped.");
    }

    @Override
    public void mainLoop() throws Exception {     
        runPolling();
    }

    public void runPolling() throws Exception {
        logger.info("runPolling on index: "+runIndex_read);

        JSONObject query = getQuery("runRanger");
        SearchResponse response = client.prepareSearch(runIndex_read).setTypes("run")
            .setSource(query).execute().actionGet();

        if (response.getHits().getTotalHits() == 0 ) { return; }
        
        for (SearchHit hit : response.getHits().getHits()) {
            String runNumber = hit.getSource().get("runNumber").toString();
            if (!runExists(runNumber)){ createRun(runNumber); } 
        }
    }

    public void createRun (String runNumber) throws Exception {

        logger.info("Started run "+ runNumber );

        String index = "_river";
        String type = "runriver_"+runNumber;

// FOR DYNAMIC MAPPING ISSUE, not working yet
//        String map = "{\"dynamic\" : true}}";

//        
//
//        PutMappingRequestBuilder pmrb = client.admin().indices()
//                        .preparePutMapping(index)
//                        .setType(type).setSource(map);
//        PutMappingResponse mresponse = pmrb.execute().actionGet();   
        //logger.info(mresponse.toString());

        IndexResponse response = client.prepareIndex(index, type, "_meta")
        .setSource(jsonBuilder()
                    .startObject()
                        .field("type", "runriver")
                        .field("runNumber", runNumber)
                        .field("role", "collector")
                        .field("es_tribe_host", es_tribe_host)
                        .field("fetching_interval", fetching_interval)
                        .field("runIndex_read", runIndex_read)
                        .field("runIndex_write", runIndex_write)
                        .field("boxinfo_write", boxinfo_write)
                    .endObject()
                  )
        .execute()
        .actionGet();

    }

    public boolean runExists(String runNumber){
        // Check if a document exists
        GetResponse response = client.prepareGet("_river", "runriver_"+runNumber, "_meta").setRefresh(true).execute().actionGet();
        return response.isExists();
    }

}

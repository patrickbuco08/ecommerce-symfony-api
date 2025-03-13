import { ApolloServer } from "@apollo/server";
import { startStandaloneServer } from "@apollo/server/standalone";

// Define GraphQL Schema
const typeDefs = `#graphql
  type Query {
    hello: String
  }
`;

// Define Resolvers
const resolvers = {
  Query: {
    hello: () => "Hello, GraphQL World!",
  },
};

// Create Apollo Server instance
const server = new ApolloServer({ typeDefs, resolvers });

// Start the server
const startServer = async () => {
  const { url } = await startStandaloneServer(server, { listen: { port: 4000 } });
  console.log(`🚀 Apollo Server running at ${url}`);
};

startServer();
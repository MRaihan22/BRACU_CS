# import random

# POPULATION_SIZE = 10
# MAX_GENERATIONS = 100
# MUTATION_RATE = 0.1


# # ------------------------------------------------
# # Create initial population
# # ------------------------------------------------
# def create_population(total_batsman):

#     population = []

#     for i in range(POPULATION_SIZE):

#         chromosome = []

#         for j in range(total_batsman):
#             gene = random.randint(0, 1)
#             chromosome.append(gene)

#         population.append(chromosome)

#     return population


# # ------------------------------------------------
# # Calculate total runs of one chromosome
# # ------------------------------------------------
# def total_runs(chromosome, batsman_list):

#     total = 0

#     for gene, batsman in zip(chromosome, batsman_list):

#         name = batsman[0]
#         run = batsman[1]

#         if gene == 1:
#             total += run

#     return total


# # ------------------------------------------------
# # Fitness function
# # ------------------------------------------------
# def fitness(chromosome, batsman_list, target_run):

#     current_run = total_runs(
#         chromosome,
#         batsman_list
#     )

#     difference = abs(
#         target_run - current_run
#     )

#     fitness_score = 1 / (1 + difference)

#     return fitness_score


# # ------------------------------------------------
# # Random parent selection
# # ------------------------------------------------
# def select_parents(population):

#     parent1, parent2 = random.sample(
#         population,
#         2
#     )

#     return parent1, parent2


# # ------------------------------------------------
# # Single-point crossover
# # ------------------------------------------------
# def crossover(parent1, parent2):

#     point = random.randint(
#         1,
#         len(parent1) - 1
#     )

#     child1 = (
#         parent1[:point]
#         +
#         parent2[point:]
#     )

#     child2 = (
#         parent2[:point]
#         +
#         parent1[point:]
#     )

#     return child1, child2


# # ------------------------------------------------
# # Mutation
# # ------------------------------------------------
# def mutate(child):

#     child = child.copy()

#     if random.random() < MUTATION_RATE:

#         index = random.randint(
#             0,
#             len(child) - 1
#         )

#         if child[index] == 0:
#             child[index] = 1
#         else:
#             child[index] = 0

#     return child


# # ------------------------------------------------
# # Genetic Algorithm
# # ------------------------------------------------
# def genetic_algorithm(
#     batsman_list,
#     target_run
# ):

#     total_batsman = len(batsman_list)

#     population = create_population(
#         total_batsman
#     )


#     for generation in range(MAX_GENERATIONS):

#         # Check if exact solution exists
#         for chromosome in population:

#             if total_runs(
#                 chromosome,
#                 batsman_list
#             ) == target_run:

#                 return chromosome


#         # ----------------------------------------
#         # Find best chromosome for elitism
#         # ----------------------------------------

#         best_chromosome = population[0]

#         best_fitness = fitness(
#             best_chromosome,
#             batsman_list,
#             target_run
#         )


#         for chromosome in population:

#             current_fitness = fitness(
#                 chromosome,
#                 batsman_list,
#                 target_run
#             )

#             if current_fitness > best_fitness:

#                 best_chromosome = chromosome

#                 best_fitness = current_fitness


#         # Keep best chromosome
#         new_population = [
#             best_chromosome.copy()
#         ]


#         # ----------------------------------------
#         # Create remaining population
#         # ----------------------------------------

#         while len(new_population) < POPULATION_SIZE:

#             parent1, parent2 = select_parents(
#                 population
#             )

#             child1, child2 = crossover(
#                 parent1,
#                 parent2
#             )

#             child1 = mutate(child1)
#             child2 = mutate(child2)


#             new_population.append(child1)


#             if len(new_population) < POPULATION_SIZE:
#                 new_population.append(child2)


#         population = new_population


#     return None


# # ------------------------------------------------
# # Input
# # ------------------------------------------------

# total_batsman, target_run = map(
#     int,
#     input().split()
# )

# batsman_list = []

# for i in range(total_batsman):

#     name, run = input().split()

#     batsman_list.append(
#         (name, int(run))
#     )


# # ------------------------------------------------
# # Run GA
# # ------------------------------------------------

# solution = genetic_algorithm(
#     batsman_list,
#     target_run
# )


# # ------------------------------------------------
# # Output
# # ------------------------------------------------

# names = []

# for batsman in batsman_list:
#     names.append(batsman[0])

# print(names)


# if solution is not None:

#     chromosome_string = ""

#     for gene in solution:
#         chromosome_string += str(gene)

#     print(chromosome_string)


#     selected_names = []

#     for gene, batsman in zip(
#         solution,
#         batsman_list
#     ):

#         if gene == 1:
#             selected_names.append(
#                 batsman[0]
#             )


#     print("Selected:", selected_names)

#     print(
#         "Total runs:",
#         total_runs(
#             solution,
#             batsman_list
#         )
#     )

# else:

#     print("No exact solution found")


import random

population_size = 20
Max_Gens = 500
Mutation_Rate = 0.1

def create_population(total_batsman):
    population = []
    for i in range(population_size):
        chromosome = []
        for j in range(total_batsman):
            gene = random.randint(0, 1)
            chromosome.append(gene)
        population.append(chromosome)
    return population


def total_runs(chromosome, batsman_list):
    total = 0
    for gene, batsman in zip(chromosome, batsman_list):
        name = batsman[0]
        run = batsman[1]

        if gene == 1:
            total += run
    return total


def fitness(chromosome, batsman_list, target_run):
    curr_run = total_runs(chromosome, batsman_list)
    difference = abs(target_run - curr_run)
    fitness_score = 1 / (1 + difference)
    return fitness_score


def select_parents(population):
    parent1, parent2 = random.sample(population, 2)
    return parent1, parent2


def crossover(parent1, parent2):
    point = random.randint(1, len(parent1) - 1)
    child1 = (parent1[:point] + parent2[point:])
    child2 = (parent2[:point] + parent1[point:])
    return child1, child2


def mutate(child):
    child = child.copy()
    if random.random() < Mutation_Rate:
        idx = random.randint(0, len(child) - 1)
        if child[idx] == 0:
            child[idx] = 1
        else:
            child[idx] = 0
    return child

def genetic_algorithm(
    batsman_list,
    target_run
):

    total_batsman = len(batsman_list)

    population = create_population(
        total_batsman
    )


    for generation in range(MAX_GENERATIONS):

        # Check if exact solution exists
        for chromosome in population:

            if total_runs(
                chromosome,
                batsman_list
            ) == target_run:

                return chromosome


        # ----------------------------------------
        # Find best chromosome for elitism
        # ----------------------------------------

        best_chromosome = population[0]

        best_fitness = fitness(
            best_chromosome,
            batsman_list,
            target_run
        )


        for chromosome in population:

            current_fitness = fitness(
                chromosome,
                batsman_list,
                target_run
            )

            if current_fitness > best_fitness:

                best_chromosome = chromosome

                best_fitness = current_fitness


        # Keep best chromosome
        new_population = [
            best_chromosome.copy()
        ]


        # ----------------------------------------
        # Create remaining population
        # ----------------------------------------

        while len(new_population) < POPULATION_SIZE:

            parent1, parent2 = select_parents(
                population
            )

            child1, child2 = crossover(
                parent1,
                parent2
            )

            child1 = mutate(child1)
            child2 = mutate(child2)


            new_population.append(child1)


            if len(new_population) < POPULATION_SIZE:
                new_population.append(child2)


        population = new_population


    return None


# ------------------------------------------------
# Input
# ------------------------------------------------

total_batsman, target_run = map(
    int,
    input().split()
)

batsman_list = []

for i in range(total_batsman):

    name, run = input().split()

    batsman_list.append(
        (name, int(run))
    )


# ------------------------------------------------
# Run GA
# ------------------------------------------------

solution = genetic_algorithm(
    batsman_list,
    target_run
)


# ------------------------------------------------
# Output
# ------------------------------------------------

names = []

for batsman in batsman_list:
    names.append(batsman[0])

print(names)


if solution is not None:

    chromosome_string = ""

    for gene in solution:
        chromosome_string += str(gene)

    print(chromosome_string)


    selected_names = []

    for gene, batsman in zip(
        solution,
        batsman_list
    ):

        if gene == 1:
            selected_names.append(
                batsman[0]
            )


    print("Selected:", selected_names)

    print(
        "Total runs:",
        total_runs(
            solution,
            batsman_list
        )
    )

else:

    print("No exact solution found")
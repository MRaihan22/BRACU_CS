import random
import numpy as np

items = [(2,6), (3,10), (1,3), (4,12), (5,18), (2,7)]

# Defining InitiaL Population
initial_population = []

for j in range(4):
    chromosome = []
    for i in range(6):
        gene = random.randint(0,1)
        chromosome.append(gene)
    initial_population.append(chromosome)
print("Initial Population is:", initial_population)


# Defining Fitness Function
def fitness(population, items, max_weight = 10):
    fitness_scores = []

    #[0, 0, 1, 0, 0, 1] [(2,6),(3,10),(1,3),(4,12),(5,18),(2,7)]
    for chromosome in population:
        total_weight = 0
        total_value = 0

        # go through each gene and corresponding item
        for gene, (w,v) in zip(chromosome, items):
           if gene == 1:        # item selected
               total_weight += w
               total_value += v

        # If capacity exceeds, Punish the Chromosome
        if total_weight > max_weight:
            total_value = 0

        fitness_scores.append(total_value)

    return fitness_scores

fit = fitness(initial_population, items, max_weight = 10)
print("Fitness of Initial Population =", fit)


# Defining Parent Selection function
def select(population, fit):
    fit = np.array(fit)
    p = fit / fit.sum()
    c = np.random.choice(len(population), p = p)
    return population[c]


# Crossover Function
def crossover(x,y):
    i = random.randint(0, len(x)-1)
    return np.array([*x[0:i], *y[i:]])


# Mutation Function
def mutate(child):
    i = random.randint(0, len(child)-1)
    if child[i] == 0:
        child[i] = 1
    else:
        child[i] = 0
    return child


# Genetic Algorithm Function
def GA(population, items):
    n = 10
    max_fit = 30
    for i in range(n):
        new_population = []
        fit = fitness(population, items)
        for j in range(len(population)):
            parent1 = select(population, fit)
            parent2 = select(population, fit)
            child = crossover(parent1, parent2)
            child = mutate(child)
            if fitness([child], items)[0] >= max_fit:
                print("One of the solutions found:", child)
                return
            new_population.append(child)
        population = np.array(new_population)

GA(initial_population, items)